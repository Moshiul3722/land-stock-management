<?php

namespace App\Livewire\Vehicles;

use App\Livewire\Concerns\ManagesCrudTable;
use App\Models\Vehicle;
use App\Models\VehicleDemand;
use App\Models\VehicleRequisition;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Fuel used by each vehicle: the fuel of approved vehicle demands (requisitions) plus the
 * drivers' fuel demands, in total per vehicle and month by month. Amounts in different
 * units (litre, kg ...) are kept apart. Filter by vehicle and year; can be printed.
 */
class FuelReport extends Component
{
    use ManagesCrudTable;

    #[Url(as: 'vehicle', except: '')]
    public string $vehicleId = '';

    /** A year, or '' for all time */
    #[Url(as: 'year')]
    public string $year = '';

    /** Month whose fuel entries are shown in the dialog (YYYY-MM) */
    public bool $showMonth = false;

    #[\Livewire\Attributes\Locked]
    public string $detailMonth = '';

    /** Vehicle whose fuel history is shown in the dialog (its number was clicked) */
    #[\Livewire\Attributes\Locked]
    public ?int $detailVehicle = null;

    protected function permissionRoutes(): array
    {
        return ['vehicle-fuel-report.index'];
    }

    public function mount(): void
    {
        $this->authorizeAccess();
        if (! array_key_exists('year', request()->query())) {
            $this->year = (string) now()->year;
        }
    }

    public function updated(): void
    {
        $this->authorizeAccess();
        if ($this->year !== '' && ! array_key_exists($this->year, $this->yearChoices)) {
            $this->year = (string) now()->year;
        }
        if ($this->vehicleId !== '' && ! array_key_exists($this->vehicleId, $this->vehicleChoices)) {
            $this->vehicleId = '';
        }
        unset($this->report);
    }

    #[Computed]
    public function vehicleChoices(): array
    {
        return Vehicle::with('type')->orderBy('vehicle_no')->get()
            ->mapWithKeys(fn ($v) => [(string) $v->id => $this->localDigits($v->vehicle_no) . ($v->type ? ' · ' . $v->type->label : '')])->all();
    }

    #[Computed]
    public function yearChoices(): array
    {
        $years = collect([now()->year])
            ->merge(VehicleRequisition::where('status', 'approved')->whereNotNull('fuel_amount')->selectRaw('DISTINCT YEAR(report_date) y')->pluck('y'))
            ->merge(VehicleDemand::where('demand_type', 'fuel')->whereNotNull('quantity')->selectRaw('DISTINCT YEAR(demand_date) y')->pluck('y'))
            ->filter()->map(fn ($y) => (int) $y)->unique()->sortDesc();

        return ['' => __('vehicle.fuel_report.all_time')] + $years->mapWithKeys(fn ($y) => [(string) $y => $this->localDigits($y)])->all();
    }

    /** Sums by vehicle and by month, from both sources, per unit. */
    #[Computed]
    public function report(): array
    {
        $vehicle = $this->vehicleId !== '' ? (int) $this->vehicleId : null;
        $year = $this->year !== '' ? (int) $this->year : null;

        $requisitions = VehicleRequisition::query()
            ->where('status', 'approved')->whereNotNull('fuel_amount')->whereNotNull('vehicle_id')
            ->when($vehicle, fn ($q) => $q->where('vehicle_id', $vehicle))
            ->when($year, fn ($q) => $q->whereYear('report_date', $year))
            ->groupBy('vehicle_id', 'fuel_unit', DB::raw("DATE_FORMAT(report_date, '%Y-%m')"))
            ->selectRaw("vehicle_id, fuel_unit AS unit, DATE_FORMAT(report_date, '%Y-%m') AS ym, SUM(fuel_amount) AS amount, COUNT(*) AS times")
            ->get();
        $demands = VehicleDemand::query()
            ->where('demand_type', 'fuel')->whereNotNull('quantity')->whereNotNull('vehicle_id')
            ->when($vehicle, fn ($q) => $q->where('vehicle_id', $vehicle))
            ->when($year, fn ($q) => $q->whereYear('demand_date', $year))
            ->groupBy('vehicle_id', 'unit', DB::raw("DATE_FORMAT(demand_date, '%Y-%m')"))
            ->selectRaw("vehicle_id, unit, DATE_FORMAT(demand_date, '%Y-%m') AS ym, SUM(quantity) AS amount, COUNT(*) AS times")
            ->get();

        $byVehicle = [];
        $byMonth = [];
        $total = ['req' => [], 'dem' => [], 'all' => []];
        foreach (['req' => $requisitions, 'dem' => $demands] as $source => $rows) {
            foreach ($rows as $row) {
                $unit = trim((string) $row->unit);
                $amount = (float) $row->amount;
                foreach ([&$byVehicle[$row->vehicle_id], &$byMonth[$row->ym], &$total] as &$bucket) {
                    $bucket[$source][$unit] = ($bucket[$source][$unit] ?? 0) + $amount;
                    $bucket['all'][$unit] = ($bucket['all'][$unit] ?? 0) + $amount;
                    $bucket['times_' . $source] = ($bucket['times_' . $source] ?? 0) + (int) $row->times;
                }
                unset($bucket);
            }
        }

        // A chosen year shows all twelve months; all time shows the months that have fuel
        if ($year) {
            $months = [];
            for ($m = 1; $m <= 12; $m++) {
                $key = sprintf('%04d-%02d', $year, $m);
                $months[$key] = $byMonth[$key] ?? [];
            }
            $byMonth = $months;
        } else {
            ksort($byMonth);
        }

        $vehicles = Vehicle::withTrashed()->with('type')->whereIn('id', array_keys($byVehicle))->get()->keyBy('id');
        uasort($byVehicle, fn ($a, $b) => array_sum($b['all'] ?? []) <=> array_sum($a['all'] ?? []));

        return ['byVehicle' => $byVehicle, 'byMonth' => $byMonth, 'total' => $total, 'vehicles' => $vehicles];
    }

    /** Open the dialog with the dated fuel entries of a month (for the chosen vehicle). */
    public function openMonth(string $ym): void
    {
        $this->authorizeAccess();
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $ym)) {
            return;
        }
        $this->detailMonth = $ym;
        $this->detailVehicle = null;
        unset($this->monthEntries);
        $this->showMonth = true;
    }

    /** Open the dialog with a vehicle's fuel history, in the chosen year (or all time). */
    public function openVehicle(int $id): void
    {
        $this->authorizeAccess();
        if (! Vehicle::withTrashed()->whereKey($id)->exists()) {
            return;
        }
        $this->detailVehicle = $id;
        $this->detailMonth = '';
        unset($this->monthEntries);
        $this->showMonth = true;
    }

    /** Heading of the dialog: the month, or the vehicle number with the period. */
    public function detailTitle(): string
    {
        if ($this->detailVehicle) {
            $no = Vehicle::withTrashed()->whereKey($this->detailVehicle)->value('vehicle_no');

            return $this->localDigits((string) $no) . ' — ' . __('vehicle.fuel_report.vehicle_history');
        }

        return $this->detailMonth ? $this->monthText($this->detailMonth) . ' — ' . __('vehicle.fuel_report.month_details') : '';
    }

    /**
     * Each time fuel was taken in the dialog's month: date, vehicle, where it came from,
     * amount and details, oldest first.
     */
    #[Computed]
    public function monthEntries(): array
    {
        if ($this->detailMonth === '' && ! $this->detailVehicle) {
            return [];
        }
        // A month, or (for a vehicle's history) the chosen year, or all time
        if ($this->detailMonth !== '') {
            $start = Carbon::createFromFormat('Y-m-d', $this->detailMonth . '-01')->startOfDay();
            $end = $start->copy()->endOfMonth();
        } elseif ($this->year !== '') {
            $start = Carbon::create((int) $this->year, 1, 1)->startOfDay();
            $end = $start->copy()->endOfYear();
        } else {
            $start = Carbon::create(1900, 1, 1);
            $end = Carbon::create(9999, 12, 31);
        }
        $vehicle = $this->detailVehicle ?: ($this->vehicleId !== '' ? (int) $this->vehicleId : null);
        $bn = app()->getLocale() === 'bn';
        $name = fn ($person) => $person ? (($bn && $person->name_bn) ? $person->name_bn : ($person->name ?: $person->name_bn)) : null;

        $entries = [];
        VehicleRequisition::query()
            ->with(['vehicle' => fn ($q) => $q->withTrashed(), 'driver' => fn ($q) => $q->withTrashed()])
            ->where('status', 'approved')->whereNotNull('fuel_amount')->whereNotNull('vehicle_id')
            ->when($vehicle, fn ($q) => $q->where('vehicle_id', $vehicle))
            ->whereBetween('report_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->each(function ($r) use (&$entries, $name) {
                $entries[] = [
                    'date' => $r->report_date, 'source' => 'req', 'id' => $r->id,
                    'vehicle' => $r->vehicle?->vehicle_no, 'driver' => $name($r->driver),
                    'amount' => [trim((string) $r->fuel_unit) => (float) $r->fuel_amount],
                    'details' => collect([$r->applicant_name, $r->destination])->filter()->join(' → '),
                ];
            });
        VehicleDemand::query()
            ->with(['vehicle' => fn ($q) => $q->withTrashed(), 'driver' => fn ($q) => $q->withTrashed()])
            ->where('demand_type', 'fuel')->whereNotNull('quantity')->whereNotNull('vehicle_id')
            ->when($vehicle, fn ($q) => $q->where('vehicle_id', $vehicle))
            ->whereBetween('demand_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->each(function ($d) use (&$entries, $name) {
                $entries[] = [
                    'date' => $d->demand_date, 'source' => 'dem', 'id' => $d->id,
                    'vehicle' => $d->vehicle?->vehicle_no, 'driver' => $name($d->driver),
                    'amount' => [trim((string) $d->unit) => (float) $d->quantity],
                    'details' => (string) $d->reason,
                ];
            });

        usort($entries, fn ($a, $b) => [$a['date']?->timestamp, $a['source'], $a['id']] <=> [$b['date']?->timestamp, $b['source'], $b['id']]);

        return $entries;
    }

    /** "20 litre, 5 kg" in local digits; '—' when nothing. */
    public function amountText(?array $sums): string
    {
        $parts = [];
        foreach ($sums ?? [] as $unit => $amount) {
            if ($amount <= 0) {
                continue;
            }
            $number = rtrim(rtrim(number_format($amount, 2, '.', ''), '0'), '.');
            $parts[] = trim($this->localDigits($number) . ' ' . $unit);
        }

        return $parts ? implode(', ', $parts) : '—';
    }

    public function monthText(string $ym): string
    {
        $bn = app()->getLocale() === 'bn';

        return $this->localDigits(Carbon::createFromFormat('Y-m-d', $ym . '-01')->locale($bn ? 'bn' : 'en')->translatedFormat('F Y'));
    }

    public function render()
    {
        return view('livewire.vehicles.fuel-report', $this->tableViewData() + [
            't' => fn (string $name) => __("vehicle.fuel_report.{$name}"),
            'report' => $this->report,
        ]);
    }
}
