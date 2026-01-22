<?php

namespace App\Http\Controllers;

use App\Models\PickupPoint;
use App\Services\BootstrapTableService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

class PickupPointController extends Controller
{
    public function index()
    {
        ResponseService::noAnyPermissionThenRedirect(['pickup-points-list', 'pickup-points-create']);
        return view('pickup-points.index');
    }

    public function create()
    {
    }

    public function store(Request $request)
    {
        ResponseService::noPermissionThenSendJson('pickup-points-create');
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:pickup_points,name',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'required|in:1,0'
        ]);

        if ($validator->fails()) {
            ResponseService::errorResponse($validator->errors()->first());
        }

        try {
            PickupPoint::create($request->except('_token'));
            ResponseService::successResponse('Data Stored Successfully');
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e, "PickupPoint Controller -> Store Method");
            ResponseService::errorResponse();
        }
    }

    public function show(Request $request)
    {
        ResponseService::noPermissionThenRedirect('pickup-points-list');
        $offset = request('offset', 0);
        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'DESC');
        $search = request('search');

        $sql = PickupPoint::with(['transportationFees']);
        
        if ($search) {
            $sql->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%$search%");
            });
        }

        $total = $sql->count();
        if ($offset >= $total && $total > 0) {
            $lastPage = floor(($total - 1) / $limit) * $limit; // calculate last page offset
            $offset = $lastPage;
        }
        $sql->orderBy($sort, $order)->skip($offset)->take($limit);
        $res = $sql->get();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $no = 1;

        foreach ($res as $row) {
            $operate = BootstrapTableService::menuEditButton('edit', route('pickup-points.update', $row->id), true);
            $operate .= BootstrapTableService::menuDeleteButton('delete', route('pickup-points.destroy', $row->id));
            $operate .= BootstrapTableService::menuButton('manage_fees', route('transportation-fees.edit',$row->id));
            $tempRow = $row->toArray();
            $tempRow['no'] = $no++;
            $tempRow['operate'] = BootstrapTableService::menuItem($operate);
            $rows[] = $tempRow;

        }
        $bulkData['rows'] = $rows;
       
        return response()->json($bulkData);
    }

    public function edit($id)
    {
    }

    public function update(Request $request, $id)
    {
        ResponseService::noPermissionThenSendJson('pickup-points-edit');
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:pickup_points,name,' . $id,
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'required|in:1,0'
        ]);

        if ($validator->fails()) {
            ResponseService::errorResponse($validator->errors()->first());
        }

        try {
            $pickupPoint = PickupPoint::findOrFail($id);
            $pickupPoint->update($request->except('_token'));
            ResponseService::successResponse('Data Updated Successfully');
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e, "PickupPoint Controller -> Update Method");
            ResponseService::errorResponse();
        }
    }

    public function destroy($id)
    {
        ResponseService::noPermissionThenSendJson('pickup-points-delete');
        try {
            $pickupPoint = PickupPoint::findOrFail($id);
            
            // Check if pickup point is being used
            if ($pickupPoint->routes()->count() > 0 || $pickupPoint->transportationFees()->count() > 0) {
                ResponseService::errorResponse('Cannot delete pickup point as it is associated with routes or transportation fees.');
            }

            $pickupPoint->delete();
            ResponseService::successResponse('Data Deleted Successfully');
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e, "PickupPoint Controller -> Delete Method");
            ResponseService::errorResponse();
        }
    }
}