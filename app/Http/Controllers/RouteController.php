<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\PickupPoint;
use App\Models\Shift;
use App\Services\BootstrapTableService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;
use App\Services\CachingService;
use App\Models\RoutePickupPoint;

class RouteController extends Controller
{
    private CachingService $cache;

    public function __construct(CachingService $cache)
    {
        $this->cache = $cache;
    }

    public function index()
    {
        ResponseService::noAnyPermissionThenRedirect(['route-list', 'route-create']);
        $pickupPoints = PickupPoint::all()->where('status', 1);
        $shifts = Shift::owner()->where('status', 1)->get();
        return view('routes.index', compact('pickupPoints', 'shifts'));
    }

    public function create()
    {
    }

    public function store(Request $request)
    {
        //dd($request->all());
        ResponseService::noPermissionThenSendJson('route-create');
        $validator = Validator::make(
            $request->all(),
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('routes')->where(function ($query) use ($request) {
                        return $query->where('shift_id', $request->shift_id);
                    }),
                ],
                'distance' => 'nullable|numeric|min:0',
                'status' => 'required|in:1,0',
                'shift_id' => 'required|exists:shifts,id',
                'pickup_points' => 'required|array',
                'pickup_points.*.pickup_point_id' => 'required|exists:pickup_points,id',
                'pickup_points.*.pickup_time' => 'required|date_format:H:i|before:pickup_points.*.drop_time',
                'pickup_points.*.drop_time' => 'required|date_format:H:i|after:pickup_points.*.pickup_time',
            ],
            [
                'pickup_points.required' => 'Please add pickup points.',
                'pickup_points.*.pickup_point_id.required' => 'Each pickup point must have a valid pickup point selected.',
                'pickup_points.*.pickup_point_id.exists' => 'The selected pickup point does not exist.',
                'pickup_points.*.pickup_time.required' => 'Each pickup point must have a valid pickup time.',
                'pickup_points.*.pickup_time.date_format' => 'Each pickup point pickup time must be in the format HH:MM.',
                'pickup_points.*.drop_time.required' => 'Each pickup point must have a valid drop time.',
                'pickup_points.*.drop_time.date_format' => 'Each pickup point drop time must be in the format HH:MM.',
                'pickup_points.*.drop_time.after' => 'Each drop time must be after the corresponding pickup time.',
                'pickup_points.*.pickup_time.before' => 'Each pickup time must be before the corresponding drop time.',
            ]
        );

        if ($validator->fails()) {
            ResponseService::errorResponse($validator->errors()->first());
        }

        try {
            $route = Route::create($request->only(['name', 'distance', 'status', 'shift_id']));
            $order = 1;
            // Attach pickup points with order
            if ($request->has('pickup_points') && is_array($request->pickup_points)) {
                foreach ($request->pickup_points as $pickupPoint) {
                    $routePickupPoint = RoutePickupPoint::create([
                        'route_id' => $route->id,
                        'pickup_point_id' => $pickupPoint['pickup_point_id'],
                        'pickup_time' => $pickupPoint['pickup_time'],
                        'drop_time' => $pickupPoint['drop_time'],
                        'order' => $order++
                    ]);
                }
            }

            ResponseService::successResponse('Data Stored Successfully');
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e, "Route Controller -> Store Method");
            ResponseService::errorResponse();
        }
    }

    public function show(Request $request)
    {
        ResponseService::noPermissionThenRedirect('route-list');

        $offset = request('offset', 0);
        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'DESC');
        $search = request('search');

        $schoolSettings = $this->cache->getSchoolSettings();
        $sql = Route::with(['routePickupPoints.pickupPoint', 'shift']);

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
            $operate = BootstrapTableService::menuButton('edit', route('routes.edit', $row->id));
            $operate .= BootstrapTableService::menuDeleteButton('delete', route('routes.destroy', $row->id));
            $operate .= BootstrapTableService::menuButton('change_order', route('routes.change-order', $row->id));

            $tempRow = $row->toArray();
            $tempRow['no'] = $no++;
            $tempRow['status'] = $row->status ? 1 : 0;
            $tempRow['operate'] = BootstrapTableService::menuItem($operate);
            $tempRow['pickup_points_count'] = $row->routePickupPoints->count();
            $tempRow['shift_name'] = $row->shift ? $row->shift->name : '-';
            $tempRow['created_at'] = $row->created_at->format($schoolSettings['date_format'] . ' ' . $schoolSettings['time_format']);
            $tempRow['updated_at'] = $row->updated_at->format($schoolSettings['date_format'] . ' ' . $schoolSettings['time_format']);
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    public function edit($id)
    {
        ResponseService::noAnyPermissionThenRedirect(['route-edit']);
        $route = Route::with(['routePickupPoints.pickupPoint'])->findOrFail($id);
        $pickupPoints = PickupPoint::all();
        $shifts = Shift::owner()->where('status', 1)->get();
        return view('routes.edit', compact('route', 'pickupPoints', 'shifts'));
    }

    public function update(Request $request, $id)
    {
        ResponseService::noPermissionThenSendJson('route-edit');

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('routes')->where(function ($query) use ($request) {
                    return $query->where('shift_id', $request->shift_id);
                })->ignore($id),
            ],
            'distance' => 'nullable|numeric|min:0',
            'status' => 'required|in:1,0',
            'shift_id' => 'required|exists:shifts,id',
            'pickup_points' => 'required|array',
            'pickup_points.*.pickup_point_id' => 'required|exists:pickup_points,id',
            'pickup_points.*.pickup_time' => 'required|date_format:H:i|before:pickup_points.*.drop_time',
            'pickup_points.*.drop_time' => 'required|date_format:H:i|after:pickup_points.*.pickup_time',
        ]);

        if ($validator->fails()) {
            ResponseService::errorResponse($validator->errors()->first());
        }

        try {
            $route = Route::findOrFail($id);
            $route->update($request->only(['name', 'distance', 'status', 'shift_id']));

            // ✅ Step 1: Process pickup points (no delete)
            if ($request->has('pickup_points') && is_array($request->pickup_points)) {

                foreach ($request->pickup_points as $pickupPoint) {
                    // Check if record already exists
                    $existing = RoutePickupPoint::where('route_id', $route->id)
                        ->where('pickup_point_id', $pickupPoint['pickup_point_id'])
                        ->first();

                    if ($existing) {
                        // ✅ Update existing record
                        $existing->update([
                            'pickup_time' => $pickupPoint['pickup_time'],
                            'drop_time' => $pickupPoint['drop_time'],
                            'order' => $pickupPoint['order'] ?? $existing->order,
                        ]);
                    } else {
                        // ✅ Add new record
                        $order = RoutePickupPoint::where('route_id', $route->id)->max('order') + 1;

                        RoutePickupPoint::create([
                            'route_id' => $route->id,
                            'pickup_point_id' => $pickupPoint['pickup_point_id'],
                            'pickup_time' => $pickupPoint['pickup_time'],
                            'drop_time' => $pickupPoint['drop_time'],
                            'order' => $pickupPoint['order'] ?? $order
                        ]);
                    }
                }

                // ✅ Optionally: remove points that are no longer in request
                $currentIds = collect($request->pickup_points)->pluck('pickup_point_id')->toArray();
                RoutePickupPoint::where('route_id', $route->id)
                    ->whereNotIn('pickup_point_id', $currentIds)
                    ->delete();
            }

            ResponseService::successResponse('Data Updated Successfully');
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e, "Route Controller -> Update Method");
            ResponseService::errorResponse();
        }
    }


    public function destroy($id)
    {
        ResponseService::noPermissionThenSendJson('route-delete');
        try {
            $route = Route::findOrFail($id);
            $route->delete();
            ResponseService::successResponse('Data Deleted Successfully');
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e, "Route Controller -> Delete Method");
            ResponseService::errorResponse();
        }
    }

    public function deletePickupPoint($id)
    {
        ResponseService::noPermissionThenSendJson('route-delete');
        try {
            $routePickupPoint = RoutePickupPoint::findOrFail($id);
            $routePickupPoint->delete();
            ResponseService::successResponse('Data Deleted Successfully');
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e, "Route Controller -> Delete Pickup Point Method");
            ResponseService::errorResponse();
        }
    }

    public function changeOrderIndex($id)
    {
        ResponseService::noAnyPermissionThenRedirect(['route-edit']);
        $route = Route::with(['routePickupPoints.pickupPoint'])->findOrFail($id);
        return view('routes.change-order', compact('route'));
    }

    public function updatePickupOrder(Request $request, $id)
    {
        ResponseService::noPermissionThenSendJson('route-edit');

        $validator = Validator::make($request->all(), [
            'pickup_points' => 'required|array',
            'pickup_points.*' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            ResponseService::errorResponse($validator->errors()->first());
        }

        try {
            foreach ($request->pickup_points as $pickupPointId => $order) {
                RoutePickupPoint::where('id', $pickupPointId)
                    ->update(['order' => (int) $order]);
            }

            ResponseService::successResponse('Pickup point order updated successfully');
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e, "Route Controller -> Update Pickup Order Method");
            ResponseService::errorResponse();
        }
    }
}