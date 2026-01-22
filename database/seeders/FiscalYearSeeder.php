<?php

namespace Database\Seeders;

use App\Models\FiscalYear;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class FiscalYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Generate for the last year, current year, and next 3 years
        $currentYear = Carbon::now()->year;
        
        // Adjust logic: If currently Jan-Jun 2025, the fiscal year started in 2024.
        // If Jul-Dec 2025, fiscal year started in 2025.
        $startBase = (Carbon::now()->month < 7) ? $currentYear - 1 : $currentYear;
        
        // Create range: 1 year back, 4 years forward
        for ($i = -1; $i < 4; $i++) {
            $year = $startBase + $i;
            $nextYear = $year + 1;
            $name = $year . '-' . $nextYear;

            FiscalYear::firstOrCreate(
                ['name' => $name],
                [
                    'start_date' => "$year-07-01",
                    'end_date' => "$nextYear-06-30",
                    'is_active' => true
                ]
            );
        }
    }
}
