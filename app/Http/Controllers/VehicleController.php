<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\TransportationPayment;
use App\Repositories\Files\FilesInterface;
use Illuminate\Http\Request;
use App\Repositories\Transportation\VehicleRepositoryInterface;
use Carbon\Carbon;
use Throwable;
use App\Services\BootstrapTableService;
use App\Services\ResponseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VehicleController extends Controller
{
    private VehicleRepositoryInterface $vehiclesRepository;
    protected FilesInterface $files;

    public function __construct(VehicleRepositoryInterface $vehiclesRepository, FilesInterface $files)
    {
        $this->vehiclesRepository = $vehiclesRepository;
        $this->files = $files;
    }
    public function index()
    {
        ResponseService::noAnyPermissionThenSendJson(['vehicles-list']);
        $vehicles = $this->vehiclesRepository->builder()->with('file')->get();
        return view('vehicles.index', compact('vehicles'));
    }

    public function store(Request $request)
    {
        ResponseService::noAnyPermissionThenSendJson(['vehicles-create']);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'vehicle_number' => 'required|string|max:100|unique:vehicles,vehicle_number',
            'capacity' => 'required|numeric|min:1',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            ResponseService::validationError($validator->errors()->first());
        }

        try {
            DB::beginTransaction();

            $vehicleData = [
                'name' => $request->name,
                'vehicle_number' => $request->vehicle_number,
                'capacity' => $request->capacity,
                'status' => $request->status ?? 1
            ];

            $vehicle = $this->vehiclesRepository->create($vehicleData);

            // Initialize the Empty Array
            $vehicleFileData = array();

            // Create A File Model Instance
            $vehicleFile = $this->files->model();

            // Get the Association Values of File with gallery
            $vehicleModelAssociate = $vehicleFile->modal()->associate($vehicle);

            if (!empty($request->images)) {

                foreach ($request->images as $key => $image) {

                    $tempFileData = array(
                        'modal_type' => $vehicleModelAssociate->modal_type,
                        'modal_id' => $vehicleModelAssociate->modal_id,
                        'file_name' => basename($image->getClientOriginalName(), '.' . $image->getClientOriginalExtension()),
                    );

                    $tempFileData['type'] = 1;
                    $tempFileData['file_thumbnail'] = null;
                    $tempFileData['file_url'] = $image;

                    $vehicleFileData[] = $tempFileData;
                }
            }

            if (!empty($request->images)) {
                $this->files->createBulk($vehicleFileData);
            }

            DB::commit();
            ResponseService::successResponse('Vehicle created successfully');
        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e, "VehicleController -> store");
            ResponseService::errorResponse();
        }
    }

    public function edit(Vehicle $vehicle)
    {
        ResponseService::noPermissionThenSendJson(['vehicles-edit']);
        return view('vehicles.edit', compact('vehicle'));
    }

    public function update(Request $request, $id)
    {
        ResponseService::noPermissionThenSendJson(['vehicles-edit']);

        $validator = Validator::make($request->all(), [
            'edit_vehicle_name' => 'required|string|max:255',
            'edit_vehicle_number' => 'required|string|max:100|unique:vehicles,vehicle_number,' . $id,
            'edit_capacity' => 'required|integer|min:1',
            'edit_status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            ResponseService::validationError($validator->errors()->first());
        }

        try {

            if ($request->edit_status == 0) {
                $vehicle = $this->vehiclesRepository->findById($id);
                $assignedCount = $vehicle->routeVehicles()->count(); // assuming relation is defined

                if ($assignedCount > 0) {
                    return ResponseService::errorResponse('Cannot change status to inactive: Vehicle is assigned to a route.');
                }
            }
            $totalAssignedStudents = TransportationPayment::whereHas('routeVehicle', function ($q) use ($id) {
                $q->where('vehicle_id', $id);
            })
                ->whereNotNull('route_vehicle_id')
                ->where('status', 'paid')
                ->count();

            if ($request->edit_capacity < $totalAssignedStudents) {
                return ResponseService::errorResponse(
                    "Cannot reduce capacity below the number of already assigned students ({$totalAssignedStudents})."
                );
            }

            $vehicleData = [
                'name' => $request->edit_vehicle_name,
                'vehicle_number' => $request->edit_vehicle_number,
                'capacity' => $request->edit_capacity,
                'status' => $request->edit_status,
            ];

            // Call repository update
            $vehicle = $this->vehiclesRepository->update($id, $vehicleData);

            // Initialize the Empty Array
            $vehicleFileData = array();

            // Create A File Model Instance
            $vehicleFile = $this->files->model();

            // Get the Association Values of File with gallery
            $vehicleModelAssociate = $vehicleFile->modal()->associate($vehicle);

            if (!empty($request->edit_images)) {

                foreach ($request->edit_images as $key => $image) {

                    $tempFileData = array(
                        'modal_type' => $vehicleModelAssociate->modal_type,
                        'modal_id' => $vehicleModelAssociate->modal_id,
                        'file_name' => basename($image->getClientOriginalName(), '.' . $image->getClientOriginalExtension()),
                    );

                    $tempFileData['type'] = 1;
                    $tempFileData['file_thumbnail'] = null;
                    $tempFileData['file_url'] = $image;

                    $vehicleFileData[] = $tempFileData;
                }
            }

            if (!empty($request->edit_images)) {
                $this->files->createBulk($vehicleFileData);
            }

            ResponseService::successResponse('Vehicle updated successfully');
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e, 'VehiclesController -> update');
            ResponseService::errorResponse();
        }
    }


    public function destroy($id)
    {
        ResponseService::noPermissionThenSendJson('vehicles-delete');

        try {
            DB::beginTransaction();

            // Find the vehicle
            $vehicle = $this->vehiclesRepository->findById($id);

            if (!$vehicle) {
                ResponseService::errorResponse('Vehicle not found.');
            }

            $assignedCount = $vehicle->routeVehicles()->count(); // assuming relation is defined

            if ($assignedCount > 0) {
                return ResponseService::errorResponse('Cannot delete: Vehicle is assigned to a route.');
            }

            // Soft delete vehicle
            $vehicle->delete();

            DB::commit();
            ResponseService::successResponse('Vehicle deleted successfully');
        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e, "VehicleController -> destroy method");
            ResponseService::errorResponse();
        }
    }

    public function restore(int $id)
    {
        ResponseService::noPermissionThenSendJson('vehicles-delete');
        try {
            // Restore soft-deleted vehicle
            $this->vehiclesRepository->findOnlyTrashedById($id)->restore();

            ResponseService::successResponse("Vehicle restored successfully");
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e, "VehicleController -> restore");
            ResponseService::errorResponse();
        }
    }

    public function trash($id)
    {
        ResponseService::noPermissionThenSendJson('vehicles-delete');

        try {
            $vehicle = $this->vehiclesRepository->builder()->withTrashed()->where('id', $id)->firstOrFail();

            $vehicle->forceDelete();

            ResponseService::successResponse("Vehicle deleted permanently");
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e, "VehicleController -> trash", 'cannot_delete_because_data_is_associated_with_other_data');
            ResponseService::errorResponse();
        }
    }

    public function show()
    {
        ResponseService::noPermissionThenRedirect('vehicles-list');
        $offset = request('offset', 0);
        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'desc');
        $search = request('search');
        $showDeleted = request('show_deleted');

        $sql = $this->vehiclesRepository->builder()->with('file')->when(!empty($showDeleted), function ($query) {
            $query->onlyTrashed();
        });

        if (!empty($search)) {
            $sql->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%")
                    ->orWhere('vehicle_number', 'LIKE', "%$search%")
                    ->orWhere('capacity', 'LIKE', "%$search%");
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
        $no = $offset + 1;

        $baseUrl = url('/');
        $baseUrlWithoutScheme = preg_replace("(^https?://)", "", $baseUrl);
        $baseUrlWithoutScheme = str_replace("www.", "", $baseUrlWithoutScheme);

        foreach ($res as $row) {
            $operate = '';
            if ($showDeleted) {
                $operate .= BootstrapTableService::menuRestoreButton('restore', route('vehicles.restore', $row->id));
                $operate .= BootstrapTableService::menuTrashButton('delete', route('vehicles.trash', $row->id));
            } else {
                $operate .= BootstrapTableService::menuEditButton('edit', route('vehicles.update', $row->id));
                $operate .= BootstrapTableService::menuDeleteButton('delete', route('vehicles.destroy', $row->id));
            }


            $tempRow = $row->toArray();
            $tempRow['no'] = $no++;
            $tempRow['status'] = $row->status;
            if ($row->file->count()){
                $tempRow['files'] = $row->file;
            }
            $tempRow['operate'] = BootstrapTableService::menuItem($operate);
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }
}
