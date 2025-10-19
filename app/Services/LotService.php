<?php

namespace App\Services;

use App\Repositories\LotRepository;
use App\Repositories\LotDetailRepository;
use App\Services\ApprovalService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class LotService
{
    protected $lotRepository;
    protected $lotDetailRepository;
    protected $approvalService;

    public function __construct(
        LotRepository $lotRepository,
        LotDetailRepository $lotDetailRepository,
        ApprovalService $approvalService
    ) {
        $this->lotRepository = $lotRepository;
        $this->lotDetailRepository = $lotDetailRepository;
        $this->approvalService = $approvalService;
    }

    /**
     * Get all lots.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function fetchAll()
    {
        // Eager load details and products for efficiency in the list view
        return $this->lotRepository->allQuery()->with('lotDetails.product')->get();
    }

    /**
     * Get a single lot by its ID.
     *
     * @param int $id
     * @return \App\Models\Lot
     */
    public function findById($id)
    {
        return $this->lotRepository->find($id)->load('lotDetails.product');
    }

    /**
     * Validate, create a new lot with its details, and submit for approval.
     *
     * @param array $data
     * @return \App\Models\Lot
     * @throws \Exception
     */
    public function store(array $data)
    {
        $validator = Validator::make($data, [
            'lot_number' => 'required|string|max:255|unique:lots',
            'entry_date' => 'required|date',
            'remarks' => 'nullable|string',
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.quantity' => 'required|integer|min:1',
            'details.*.unit_price' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        return DB::transaction(function () use ($data) {
            try {
                // 1. Create the main Lot record
                $lot = $this->lotRepository->create([
                    'lot_number' => $data['lot_number'],
                    'entry_date' => $data['entry_date'],
                    'remarks' => $data['remarks'],
                    'status' => 'pending', // Initial status
                ]);

                // 2. Create the LotDetail records
                foreach ($data['details'] as $item) {
                    $item['lot_id'] = $lot->id;
                    $this->lotDetailRepository->create($item);
                }

                // 3. Submit the newly created lot to the approval workflow
                $this->approvalService->submitForApproval($lot, 'Lot Approval');

                return $lot;
            } catch (Exception $e) {
                Log::error($e->getMessage());
                throw new InvalidArgumentException('Unable to create lot: ' . $e->getMessage());
            }
        });
    }

    /**
     * Validate and update an existing lot and its details.
     *
     * @param array $data
     * @param int $id
     * @return \App\Models\Lot
     * @throws \Exception
     */
    public function update(array $data, $id)
    {
        $validator = Validator::make($data, [
            'lot_number' => 'required|string|max:255|unique:lots,lot_number,' . $id,
            'entry_date' => 'required|date',
            'remarks' => 'nullable|string',
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.quantity' => 'required|integer|min:1',
            'details.*.unit_price' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        return DB::transaction(function () use ($data, $id) {
            try {
                // Update the main Lot record
                $lot = $this->lotRepository->update([
                    'lot_number' => $data['lot_number'],
                    'entry_date' => $data['entry_date'],
                    'remarks' => $data['remarks'],
                ], $id);

                // Delete old details to replace them
                $lot->lotDetails()->delete();

                // Create the new LotDetail records
                foreach ($data['details'] as $item) {
                    $item['lot_id'] = $lot->id;
                    $this->lotDetailRepository->create($item);
                }

                return $lot;
            } catch (Exception $e) {
                Log::error($e->getMessage());
                throw new InvalidArgumentException('Unable to update lot');
            }
        });
    }

    /**
     * Delete a lot by its ID.
     *
     * @param int $id
     * @return bool|mixed|null
     * @throws \Exception
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $result = $this->lotRepository->delete($id);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Unable to delete lot');
        }
        DB::commit();

        return $result;
    }
}
