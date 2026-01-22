<?php

namespace App\Http\Controllers;

use App\Models\TransportationFee;
use App\Models\PickupPoint;
use App\Services\BootstrapTableService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;
use Illuminate\Support\Facades\DB;

class TransportationFeeController extends Controller
{
    public function edit($id)
    {
        ResponseService::noAnyPermissionThenRedirect(['transportation-fees-edit']);
        $pickupPoints = PickupPoint::where('status', 1)->orderBy('name')->get();
        $transportationFees = PickupPoint::with(['transportationFees'])->where('id', $id)->first();
        return view('pickup-points.transportation_fee', compact('transportationFees', 'pickupPoints'));
    }

    public function update(Request $request)
    {
        //dd($request->fees);
        ResponseService::noPermissionThenSendJson('transportation-fees-edit');

        $validator = Validator::make($request->all(), [
            'pickup_point_id' => 'required|exists:pickup_points,id',
            'edit_fees' => 'nullable|array',
            'edit_fees.*.duration' => 'required_with:edit_fees.*.id|numeric|min:0',
            'edit_fees.*.fee_amount' => 'required_with:edit_fees.*.id|numeric|min:0',
            'fees' => 'nullable|array',
            'fees.*.duration' => 'required|numeric|min:0',
            'fees.*.fee_amount' => 'required|numeric|min:0'
        ]);

        $validator->after(function ($validator) use ($request) {
            // Safely get edit_fees and fees as arrays, defaulting to empty array if null
            $editFees = is_array($request->edit_fees) ? $request->edit_fees : [];
            $fees = $request->has('fees') && is_array($request->fees) ? $request->fees : [];

            // Merge all durations into one array
            $allDurations = array_merge(
                array_column($editFees, 'duration'),
                array_column($fees, 'duration')
            );

            // Check for duplicate durations in the request itself
            $counts = array_count_values($allDurations);
            $duplicates = array_keys(array_filter($counts, fn($count) => $count > 1));
            if ($duplicates) {
                ResponseService::errorResponse('Duplicate duration(s) in request.');
            }

            $conflictExists = TransportationFee::where('pickup_point_id', $request->pickup_point_id)
                ->whereIn('duration', $allDurations)
                ->where('pickup_point_id', '!=', $request->id) // Exclude current pickup point
                ->exists();

            if ($conflictExists) {
                ResponseService::errorResponse('One or more durations already exist for another pickup point.');
            }
        });

        if ($validator->fails()) {
            return ResponseService::errorResponse($validator->errors()->first());
        }

        try {
            DB::beginTransaction();
            // Update existing fees
            if (!empty($request->edit_fees)) {
                foreach ($request->edit_fees as $key => $item) {
                    TransportationFee::where('id', $item['id'])->update([
                        'pickup_point_id' => $request->pickup_point_id,
                        'duration' => $item['duration'],
                        'fee_amount' => $item['fee_amount']
                    ]);
                }
            }
           
            // Insert new fees in bulk
            if (!empty($request->fees)) {
                foreach ($request->fees as $key => $item) {
                    $newFees[] = [
                        'pickup_point_id' => $request->pickup_point_id,
                        'duration' => $item['duration'],
                        'fee_amount' => $item['fee_amount']
                    ];
                }

                TransportationFee::insert($newFees);
            }

            DB::commit();
            ResponseService::successResponse('Transportation fees updated successfully');
        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e, "TransportationFee Controller -> Update Method");
            return ResponseService::errorResponse();
        }
    }


    public function destroy($id)
    {
        ResponseService::noPermissionThenSendJson('transportation-fees-delete');
        try {
            DB::beginTransaction();

            // Delete all transportation fees for this pickup point
            TransportationFee::findOrFail($id)->delete();

            DB::commit();
            ResponseService::successResponse('Data Deleted Successfully');
        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e, "TransportationFee Controller -> Destroy Method");
            ResponseService::errorResponse();
        }
    }
}
