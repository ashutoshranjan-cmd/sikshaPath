<?php

namespace App\Http\Controllers;

use App\Repositories\Holiday\HolidayInterface;
use App\Repositories\SessionYear\SessionYearInterface;
use App\Services\BootstrapTableService;
use App\Services\ResponseService;
use App\Services\SessionYearsTrackingsService;
use App\Services\CachingService;
use App\Models\StaffAttendance;
use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Throwable;
use Illuminate\Support\Facades\DB;

class HolidayController extends Controller
{

    private HolidayInterface $holiday;
    private SessionYearInterface $sessionYear;
    private SessionYearsTrackingsService $sessionYearsTrackingsService;
    private CachingService $cache;

    public function __construct(HolidayInterface $holiday, SessionYearInterface $sessionYear, SessionYearsTrackingsService $sessionYearsTrackingsService, CachingService $cache)
    {
        $this->holiday = $holiday;
        $this->sessionYear = $sessionYear;
        $this->sessionYearsTrackingsService = $sessionYearsTrackingsService;
        $this->cache = $cache;
    }

    public function index()
    {
        ResponseService::noFeatureThenRedirect('Holiday Management');
        ResponseService::noPermissionThenRedirect('holiday-list');
        $sessionYears = $this->sessionYear->all();
        $current_sessionYear = $this->cache->getDefaultSessionYear();
        $months = sessionYearWiseMonth();
        $schoolSettings = $this->cache->getSchoolSettings();
        return view('holiday.index', compact('sessionYears', 'months', 'current_sessionYear', 'schoolSettings'));
    }


    public function store(Request $request)
    {
        ResponseService::noFeatureThenRedirect('Holiday Management');
        ResponseService::noPermissionThenRedirect('holiday-create');

        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'title' => 'required|string',
        ]);

        if ($validator->fails()) {
            ResponseService::errorResponse($validator->errors()->first());
        }

        try {
            DB::beginTransaction();

            $sessionYear = $this->cache->getDefaultSessionYear();
            $schoolSettings = $this->cache->getSchoolSettings();

            $holidayDate = Carbon::parse($request->date)->startOfDay();
            $holidayYmd = $holidayDate->format('Y-m-d');
            $todayYmd = now()->format('Y-m-d');

            $sessionStart = Carbon::createFromFormat(
                $schoolSettings['date_format'],
                $sessionYear->start_date
            )->startOfDay();

            $sessionEnd = Carbon::createFromFormat(
                $schoolSettings['date_format'],
                $sessionYear->end_date
            )->endOfDay();

            // 🔒 Ensure holiday date is inside session year
            if ($holidayDate->lt($sessionStart) || $holidayDate->gt($sessionEnd)) {
                ResponseService::errorResponse(
                    'The selected date must fall within the current session year.'
                );
            }

            // 🚫 Do not allow holiday if staff attendance already exists
            $staffAttendanceExists = StaffAttendance::whereDate('date', $holidayYmd)->exists();
            if ($staffAttendanceExists) {
                ResponseService::errorResponse(
                    'Cannot add holiday on a date with existing staff attendance records.'
                );
            }

            // 🔍 Find approved leaves overlapping the holiday
            $leaves = Leave::where('status', 1) // approved
                ->where('from_date', '<=', $holidayYmd)
                ->where('to_date', '>=', $holidayYmd)
                ->get();

            $notifyUsers = [];

            if ($leaves->isNotEmpty()) {
                foreach ($leaves as $leave) {

                    // Reject ONLY future leaves
                    if ($leave->from_date > $todayYmd) {
                        $leave->status = 2; // rejected
                        $leave->save();

                        $notifyUsers[] = $leave->user_id;
                    }
                }
            }

            // ✅ Create holiday
            $holiday = $this->holiday->create($request->all());

            // 🔗 Track session year
            $this->sessionYearsTrackingsService->storeSessionYearsTracking(
                'App\Models\Holiday',
                $holiday->id,
                Auth::id(),
                $sessionYear->id,
                Auth::user()->school_id,
                null
            );

            // 🔔 Notify affected users
            if (!empty($notifyUsers)) {
                send_notification(
                    $notifyUsers,
                    'Leave Rejected',
                    'Your leave request has been rejected because the selected date range includes a holiday on ' .
                    $holidayDate->format('Y-m-d') .
                    '. Please revise and resubmit your request.',
                    'holiday_leave_rejection'
                );
            }

            DB::commit();
            ResponseService::successResponse('Holiday added successfully');

        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e, "Holiday Controller -> Store Method");
            ResponseService::errorResponse();
        }
    }

    public function update($id, Request $request)
    {
        ResponseService::noFeatureThenRedirect('Holiday Management');
        ResponseService::noPermissionThenSendJson('holiday-edit');
        $validator = Validator::make($request->all(), ['date' => 'required', 'title' => 'required',]);

        if ($validator->fails()) {
            ResponseService::errorResponse($validator->errors()->first());
        }
        try {
            $this->holiday->update($id, $request->all());
            $sessionYear = $this->cache->getDefaultSessionYear();
            $this->sessionYearsTrackingsService->storeSessionYearsTracking('App\Models\Holiday', $id, Auth::user()->id, $sessionYear->id, Auth::user()->school_id, null);
            ResponseService::successResponse('Data Updated Successfully');
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e, "Holiday Controller -> Update Method");
            ResponseService::errorResponse();
        }
    }

    // TODO : Remove this if not necessary
    // public function holiday_view()
    // {
    //     return view('holiday.list');
    // }

    public function show(Request $request)
    {
        ResponseService::noFeatureThenRedirect('Holiday Management');
        ResponseService::noPermissionThenRedirect('holiday-list');
        $offset = request('offset', 0);
        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'DESC');
        $search = request('search');
        $session_year_id = request('session_year_id');
        $month = request('month');

        $sessionYear = $this->sessionYear->findById($session_year_id);
        $schoolSettings = $this->cache->getSchoolSettings();

        $sql = $this->holiday->builder()
            ->where(function ($query) use ($search) {
                $query->when($search, function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('id', 'LIKE', "%$search%")->orwhere('title', 'LIKE', "%$search%")->orwhere('description', 'LIKE', "%$search%")->orwhere('date', 'LIKE', "%$search%");
                    });
                });
            })->when($session_year_id, function ($query) use ($sessionYear, $schoolSettings) {
                $query->whereDate('date', '>=', Carbon::createFromFormat($schoolSettings['date_format'],$sessionYear->start_date))
                    ->whereDate('date', '<=', Carbon::createFromFormat($schoolSettings['date_format'],$sessionYear->end_date));
            })->when($month, function ($query) use ($month) {
                $query->whereMonth('date', $month);
            });

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
            $operate = BootstrapTableService::editButton(route('holiday.update', $row->id));
            $operate .= BootstrapTableService::deleteButton(route('holiday.destroy', $row->id));
            $tempRow = $row->toArray();
            $tempRow['no'] = $no++;
            // $tempRow['date'] = format_date($row->date);
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    public function destroy($id)
    {
        ResponseService::noFeatureThenRedirect('Holiday Management');
        ResponseService::noPermissionThenSendJson('holiday-delete');
        try {
            $this->holiday->deleteById($id);
            $sessionYear = $this->cache->getDefaultSessionYear();
            $this->sessionYearsTrackingsService->deleteSessionYearsTracking('App\Models\Holiday', $id, Auth::user()->id, $sessionYear->id, Auth::user()->school_id, null);
            ResponseService::successResponse('Data Deleted Successfully');
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e, "Holiday Controller -> Delete Method");
            ResponseService::errorResponse();
        }
    }
}
