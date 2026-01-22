<?php

namespace App\Http\Controllers;

use App\Models\SessionYear;
use App\Models\Expense;
use App\Models\Leave;
use App\Repositories\StaffAttendance\StaffAttendanceInterface;
use App\Repositories\Staff\StaffInterface;
use App\Repositories\LeaveMaster\LeaveMasterInterface;
use App\Repositories\Leave\LeaveInterface;
use App\Repositories\LeaveDetail\LeaveDetailInterface;
use App\Services\BootstrapTableService;
use App\Services\CachingService;
use App\Services\ResponseService;
use App\Services\SessionYearsTrackingsService;
use App\Models\Holiday;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;
use function PHPUnit\Framework\isEmpty;

class StaffAttendanceController extends Controller
{

    private StaffAttendanceInterface $staffAttendance;
    private StaffInterface $staff;
    private CachingService $cache;
    private SessionYearsTrackingsService $sessionYearsTrackingsService;
    private LeaveMasterInterface $leaveMaster;
    private LeaveInterface $leave;
    private LeaveDetailInterface $leaveDetail;

    public function __construct(StaffAttendanceInterface $staffAttendance, StaffInterface $staff, CachingService $cachingService, SessionYearsTrackingsService $sessionYearsTrackingsService, LeaveMasterInterface $leaveMaster, LeaveInterface $leave, LeaveDetailInterface $leaveDetail)
    {
        $this->staffAttendance = $staffAttendance;
        $this->staff = $staff;
        $this->cache = $cachingService;
        $this->sessionYearsTrackingsService = $sessionYearsTrackingsService;
        $this->leaveMaster = $leaveMaster;
        $this->leave = $leave;
        $this->leaveDetail = $leaveDetail;
    }

    public function index()
    {
        ResponseService::noFeatureThenRedirect('Staff Attendance Management');
        ResponseService::noAnyPermissionThenRedirect(['staff-attendance-list']);

        return view('staff-attendance.index');
    }

    public function view()
    {
        ResponseService::noFeatureThenRedirect('Staff Attendance Management');
        ResponseService::noAnyPermissionThenRedirect(['staff-attendance-list']);

        return view('staff-attendance.view');
    }

    public function getAttendanceData(Request $request)
    {
        ResponseService::noFeatureThenRedirect('Staff Attendance Management');
        ResponseService::noAnyPermissionThenRedirect(['staff-attendance-list']);
        $sessionYear = $this->cache->getDefaultSessionYear();
        if ($request->mode == 'monthly') {
            $startDate = Carbon::createFromDate($request->year, $request->month, 1)->startOfMonth();
            $endDate = Carbon::createFromDate($request->year, $request->month, 1)->endOfMonth();
            $attendance = $this->staffAttendance->builder()
                ->with(['user:id,first_name,last_name,email,image'])
                ->where('session_year_id', $sessionYear->id)
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->when($request->search, function ($query) use ($request) {
                    $query->whereHas('user', function ($q) use ($request) {
                        $q->where('first_name', 'like', '%' . $request->search . '%')
                            ->orWhere('last_name', 'like', '%' . $request->search . '%')
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE '%" . $request->search . "%'");
                    });
                })
                ->get(['id', 'staff_id', 'date', 'type']);
            $holidayAttendance = Holiday::where('date', '>=', $startDate->format('Y-m-d'))
                ->where('date', '<=', $endDate->format('Y-m-d'))
                ->get();
            $leaves = $this->leave->builder()
                ->where('status', 1)
                ->with([
                    'leave_detail' => function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
                    }
                ])
                ->get();
            $weeklyOffDays = $this->leaveMaster->builder()
                ->where('session_year_id', $this->cache->getDefaultSessionYear()->id)
                ->pluck('holiday')
                ->filter()
                ->flatMap(function ($day) {
                    return collect(explode(',', $day))
                        ->map(fn($d) => ucfirst(strtolower(trim($d))));
                })
                ->unique()
                ->values();

            // 4️⃣ Generate all weekly-off dates in the selected month
            $weeklyOffDates = collect();
            $current = $startDate->copy();
            $end = $endDate->copy();

            while ($current->lte($end)) {
                if ($weeklyOffDays->contains($current->format('l'))) {
                    $weeklyOffDates->push($current->format('d-m-Y'));
                }
                $current->addDay();
            }

            // 5️⃣ Add weekly-off dates to holiday list (no duplicates)
            foreach ($weeklyOffDates as $woDate) {
                if (!$holidayAttendance->contains(fn($h) => data_get($h, 'date') === $woDate)) {
                    $holidayAttendance->push((object) [
                        'dmyFormat' => $woDate,
                        'title' => Carbon::parse($woDate)->format('l') . ' (Weekly Off)'
                    ]);
                }
            }
            $staff = Staff::with([
                'user:id,first_name,last_name,email,image'
            ])
                ->whereHas('user', function ($q) {
                    $q->where('status', 1)->whereNull('deleted_at');
                })
                ->when($request->search, function ($query) use ($request) {
                    $query->whereHas('user', function ($q) use ($request) {
                        $q->where('first_name', 'like', '%' . $request->search . '%')
                            ->orWhere('last_name', 'like', '%' . $request->search . '%')
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$request->search}%"]);
                    });
                })
                ->get(['id', 'user_id']);
            $responseData = [
                'success' => true,
                'staff' => $staff,
                'attendance' => $attendance,
                'holiday' => $holidayAttendance,
                'leaves' => $leaves,
            ];
            return response()->json($responseData);
        } else {
            $response = $this->staffAttendance->builder()->select('type')->where(['date' => date('Y-m-d', strtotime($request->date)), 'session_year_id' => $sessionYear->id])->pluck('type')->first();
            return response()->json($response);
        }
    }

    public function store(Request $request)
    {
        ResponseService::noFeatureThenRedirect('Staff Attendance Management');
        ResponseService::noAnyPermissionThenRedirect(['staff-attendance-edit']);

        $request->validate(['date' => 'required']);

        try {
            DB::beginTransaction();

            $sessionYear = $this->cache->getDefaultSessionYear();
            $dateYmd = date('Y-m-d', strtotime($request->date));
            $attendanceMonth = Carbon::parse($dateYmd)->month;
            $attendanceYear = Carbon::parse($dateYmd)->year;

            $holiday = Holiday::where('date', $dateYmd)->first();
            if ($holiday) {
                DB::rollBack();
                return ResponseService::errorResponse(
                    "The selected date ($dateYmd) is marked as holiday ({$holiday->title}). Attendance cannot be modified."
                );
            }
            $leaveMasterHoliday = $this->leaveMaster->builder()
                ->where('session_year_id', $sessionYear->id)
                ->value('holiday');
            if ($leaveMasterHoliday) {
                $holidayDays = explode(',', $leaveMasterHoliday);
                $dayName = Carbon::parse($dateYmd)->format('l');
                if (in_array($dayName, $holidayDays)) {
                    DB::rollBack();
                    return ResponseService::errorResponse(
                        "Attendance cannot be modified on $dayName."
                    );
                }
            }
            $attendanceRows = [];
            $absentUsers = [];

            /**
             * SAFE HELPERS (NO refresh(), NO broken relations)
             */

            // Create a new leave_detail
            $mkDetail = function (int $leaveId, string $type) use ($dateYmd) {
                return $this->leaveDetail->create([
                    'leave_id' => $leaveId,
                    'date' => $dateYmd,
                    'type' => $type,
                    'school_id' => Auth::user()->school_id,
                ]);
            };

            $reason = $request->attendance_data[0]['reason'] ?? 'System: Attendance';
            // Create complete leave + detail
            $mkLeaveWithDetail = function (int $userId, string $type) use ($dateYmd, $mkDetail, $reason) {
                $leave = $this->leave->create([
                    'user_id' => $userId,
                    'reason' => $reason,
                    'from_date' => $dateYmd,
                    'to_date' => $dateYmd,
                    'leave_master_id' => 1,
                    'status' => 1,
                    'school_id' => Auth::user()->school_id,
                ]);

                $detail = $mkDetail($leave->id, $type);
                return [$leave, $detail];
            };

            // SAFE delete detail + parent if needed (NO relation calls)
            $deleteDetailAndCascade = function ($detail) {
                if (!$detail)
                    return;

                $leaveId = $detail->leave_id;  // capture ID safely
                $detail->delete();

                $leave = Leave::find($leaveId);
                if ($leave && $leave->leave_detail()->count() == 0) {
                    $leave->delete();
                }
            };

            // Helper: find first detail of given type
            $firstByType = function ($details, string $type) {
                return $details->firstWhere('detail.type', $type);
            };

            $isBulk = count($request->attendance_data) > 1;
            $skippedStaffCount = 0;
            $singleSkipped = false;


            foreach ($request->attendance_data as $row) {

                $staffId = (int) $row['staff_id'];
                $attendanceType = (int) ($row['type'] ?? 1);
                $reason = $row['reason'] ?? null;
                $staffIds = $this->staff->builder()->where('user_id', $staffId)->first();
                $payrollExists = Expense::where('staff_id', $staffIds->id)
                    ->where('month', $attendanceMonth)
                    ->where('year', $attendanceYear)
                    ->exists();

                if ($payrollExists) {

                    if ($isBulk) {
                        $skippedStaffCount++;
                    } else {
                        $singleSkipped = true;
                    }

                    continue; // skip processing this staff
                }

                // ✅ HOLIDAY (type=3, do NOT touch leaves)
                if ($attendanceType === 3) {
                    $attendanceRows[] = [
                        'id' => $row['id'] ?? null,
                        'staff_id' => $staffId,
                        'session_year_id' => $sessionYear->id,
                        'type' => 3,
                        'date' => $dateYmd,
                        'reason' => null,
                        'leave_id' => null,
                        'leave_detail_id' => null,
                    ];
                    continue;
                }

                // ✅ Load all leaves for the date
                $leaves = $this->leave->builder()
                    ->where('user_id', $staffId)
                    ->where('from_date', '<=', $dateYmd)
                    ->where('to_date', '>=', $dateYmd)
                    ->where('status', 1)
                    ->with(['leave_detail' => fn($q) => $q->where('date', $dateYmd)])
                    ->get();

                // ✅ Existing attendance record (if any)
                $attendance = $this->staffAttendance->builder()
                    ->where('staff_id', $staffId)
                    ->where('date', $dateYmd)
                    ->first();

                // ✅ Split leave details into admin vs attendance-created
                $adminDetails = collect();
                $attnDetails = collect();

                foreach ($leaves as $leave) {
                    foreach ($leave->leave_detail as $d) {
                        if ($attendance && $attendance->leave_detail_id == $d->id) {
                            $attnDetails->push(['leave' => $leave, 'detail' => $d]);
                        } else {
                            $adminDetails->push(['leave' => $leave, 'detail' => $d]);
                        }
                    }
                }

                $has = function ($set, $type) {
                    return $set->firstWhere('detail.type', $type) !== null;
                };

                // Flags
                $adminHasFull = $has($adminDetails, 'Full');
                $adminHasFirst = $has($adminDetails, 'First Half');
                $adminHasSecond = $has($adminDetails, 'Second Half');

                $attnHasFull = $has($attnDetails, 'Full');
                $attnHasFirst = $has($attnDetails, 'First Half');
                $attnHasSecond = $has($attnDetails, 'Second Half');

                $leaveIdForAttendance = null;
                $leaveDetailIdForAttn = null;

                /**
                 * ✅ APPLY ATTENDANCE LOGIC
                 */

                switch ($attendanceType) {

                    // ✅ FULL PRESENT → delete ALL attendance-created details
                    case 1:
                        foreach (['Full', 'First Half', 'Second Half'] as $t) {
                            $node = $firstByType($attnDetails, $t);
                            if ($node)
                                $deleteDetailAndCascade($node['detail']);
                        }
                        break;

                    // ✅ FULL ABSENT
                    case 0:
                        $absentUsers[] = $staffId;

                        // CASE A: Admin already full → attendance creates nothing
                        if ($adminHasFull || ($adminHasFirst && $adminHasSecond)) {
                            foreach (['Full', 'First Half', 'Second Half'] as $t) {
                                $node = $firstByType($attnDetails, $t);
                                if ($node)
                                    $deleteDetailAndCascade($node['detail']);
                            }
                        }

                        // CASE B: Admin First only → attendance creates Second
                        elseif ($adminHasFirst && !$adminHasSecond) {

                            foreach (['Full', 'First Half'] as $t) {
                                $node = $firstByType($attnDetails, $t);
                                if ($node)
                                    $deleteDetailAndCascade($node['detail']);
                            }

                            $node = $firstByType($attnDetails, 'Second Half');
                            if ($node) {
                                $leaveIdForAttendance = $node['leave']->id;
                                $leaveDetailIdForAttn = $node['detail']->id;
                            } else {
                                [$leave, $detail] = $mkLeaveWithDetail($staffId, 'Second Half');
                                $leaveIdForAttendance = $leave->id;
                                $leaveDetailIdForAttn = $detail->id;
                            }
                        }

                        // CASE C: Admin Second only → attendance creates First
                        elseif ($adminHasSecond && !$adminHasFirst) {

                            foreach (['Full', 'Second Half'] as $t) {
                                $node = $firstByType($attnDetails, $t);
                                if ($node)
                                    $deleteDetailAndCascade($node['detail']);
                            }

                            $node = $firstByType($attnDetails, 'First Half');
                            if ($node) {
                                $leaveIdForAttendance = $node['leave']->id;
                                $leaveDetailIdForAttn = $node['detail']->id;
                            } else {
                                [$leave, $detail] = $mkLeaveWithDetail($staffId, 'First Half');
                                $leaveIdForAttendance = $leave->id;
                                $leaveDetailIdForAttn = $detail->id;
                            }
                        }

                        // CASE D: No admin leaves → attendance creates full
                        else {
                            // convert half to full
                            if ($attnHasFirst || $attnHasSecond) {
                                $node = $attnHasFirst ? $firstByType($attnDetails, 'First Half')
                                    : $firstByType($attnDetails, 'Second Half');

                                $oldLeaveId = $node['leave']->id;
                                $deleteDetailAndCascade($node['detail']);

                                $leave = Leave::find($oldLeaveId);

                                if (!$leave) { // deleted by cascade
                                    [$leave, $detail] = $mkLeaveWithDetail($staffId, 'Full');
                                } else {
                                    $detail = $mkDetail($leave->id, 'Full');
                                }

                                $leaveIdForAttendance = $leave->id;
                                $leaveDetailIdForAttn = $detail->id;
                            }

                            // no attendance leave → create new full
                            elseif (!$attnHasFull) {
                                [$leave, $detail] = $mkLeaveWithDetail($staffId, 'Full');
                                $leaveIdForAttendance = $leave->id;
                                $leaveDetailIdForAttn = $detail->id;
                            } elseif ($attnHasFull) {
                                $node = $firstByType($attnDetails, 'Full');
                                $leaveIdForAttendance = $node['leave']->id;
                                $leaveDetailIdForAttn = $node['detail']->id;
                            }
                        }

                        break;

                    // ✅ FIRST HALF PRESENT → attendance creates Second Half
                    case 4:
                        // admin already second → enforce admin
                        if ($adminHasSecond) {
                            foreach (['First Half', 'Full'] as $t) {
                                $node = $firstByType($attnDetails, $t);
                                if ($node)
                                    $deleteDetailAndCascade($node['detail']);
                            }
                        } else {
                            // remove conflicts
                            foreach (['First Half', 'Full'] as $t) {
                                $node = $firstByType($attnDetails, $t);
                                if ($node)
                                    $deleteDetailAndCascade($node['detail']);
                            }

                            $node = $firstByType($attnDetails, 'Second Half');
                            if ($node) {
                                $leaveIdForAttendance = $node['leave']->id;
                                $leaveDetailIdForAttn = $node['detail']->id;
                            } else {
                                [$leave, $detail] = $mkLeaveWithDetail($staffId, 'Second Half');
                                $leaveIdForAttendance = $leave->id;
                                $leaveDetailIdForAttn = $detail->id;
                            }
                        }
                        break;

                    // ✅ SECOND HALF PRESENT → attendance creates First Half
                    case 5:
                        if ($adminHasFirst) {
                            foreach (['Second Half', 'Full'] as $t) {
                                $node = $firstByType($attnDetails, $t);
                                if ($node)
                                    $deleteDetailAndCascade($node['detail']);
                            }
                        } else {
                            foreach (['Second Half', 'Full'] as $t) {
                                $node = $firstByType($attnDetails, $t);
                                if ($node)
                                    $deleteDetailAndCascade($node['detail']);
                            }

                            $node = $firstByType($attnDetails, 'First Half');
                            if ($node) {
                                $leaveIdForAttendance = $node['leave']->id;
                                $leaveDetailIdForAttn = $node['detail']->id;
                            } else {
                                [$leave, $detail] = $mkLeaveWithDetail($staffId, 'First Half');
                                $leaveIdForAttendance = $leave->id;
                                $leaveDetailIdForAttn = $detail->id;
                            }
                        }
                        break;
                }
                // If attendance reason exists AND leave is linked → update the leave reason
                if (!empty($reason) && $leaveIdForAttendance) {

                    // Always update the parent leave reason
                    Leave::where('id', $leaveIdForAttendance)
                        ->update(['reason' => $reason]);
                }
                // ✅ BUILD ATTENDANCE ROW
                $attendanceRows[] = [
                    'id' => $row['id'] ?? null,
                    'staff_id' => $staffId,
                    'session_year_id' => $sessionYear->id,
                    'type' => $attendanceType,
                    'date' => $dateYmd,
                    'reason' => $reason,
                    'leave_id' => $leaveIdForAttendance,
                    'leave_detail_id' => $leaveDetailIdForAttn,
                ];
            }

            // ✅ Upsert
            $this->staffAttendance->upsert(
                $attendanceRows,
                ['id'],
                ['staff_id', 'session_year_id', 'type', 'date', 'reason', 'leave_id', 'leave_detail_id']
            );

            DB::commit();

            if ($request->absent_notification && !empty($absentUsers)) {
                $d = Carbon::parse($dateYmd)->format('F jS, Y');
                send_notification($absentUsers, 'Absent', "You are marked absent on $d", 'attendance');
            }

            if ($isBulk) {

                if ($skippedStaffCount > 0) {
                    ResponseService::successResponse(
                        "Data Stored Successfully — {$skippedStaffCount} staff skipped due to payroll lock"
                    );
                } else {
                    ResponseService::successResponse("Data Stored Successfully");
                }

            } else { // single staff mode

                if ($singleSkipped) {
                    ResponseService::errorResponse(
                        "Attendance skipped — payroll for this month is already generated"
                    );
                } else {
                    ResponseService::successResponse("Attendance Stored Successfully");
                }

            }

        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e, "Staff Attendance Controller -> Store");
            ResponseService::errorResponse();
        }
    }

    public function show(Request $request)
    {
        ResponseService::noFeatureThenRedirect('Staff Attendance Management');
        ResponseService::noAnyPermissionThenRedirect(['staff-attendance-list']);

        $date = date('Y-m-d', strtotime($request->date));
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'ASC');
        $search = $request->input('search');

        /* -------------------------
            SESSION + MASTER LEAVE
        --------------------------*/
        $sessionYear = $this->cache->getDefaultSessionYear();
        $leaveMaster = $this->leaveMaster->builder()
            ->where('session_year_id', $sessionYear->id)
            ->first();

        $holiday_days = $leaveMaster->holiday ?? null;

        /* -------------------------
            HOLIDAYS
        --------------------------*/

        $holidays = Holiday::where('date', $date)->first();
        if ($holidays != null) {
            $holidays = true;
        }
        /* -------------------------
             FETCH ATTENDANCE
        --------------------------*/
        $attendanceRecords = $this->staffAttendance->builder()
            ->with('user.staff')
            ->where('date', $date)
            ->whereHas('user', fn($q) => $q->where('status', 1)->whereNull('deleted_at'))
            ->orderBy($sort, $order)
            ->get()
            ->keyBy('staff_id');

        /* -------------------------
                 FETCH STAFF
        --------------------------*/
        $staffQuery = $this->staff->builder()->with([
            'user',
            'leave' => fn($q) => $q->with([
                'leave_detail' => fn($d) => $d->where('date', $date)
            ])
                ->where('from_date', '<=', $date)
                ->where('to_date', '>=', $date)
                ->where('status', 1)
        ])->whereHas('user', function ($q) {
            $q->where('status', 1)->whereNull('deleted_at');
        });

        if ($search) {
            $staffQuery->where(function ($q) use ($search) {
                $q->where('user_id', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhereRaw("CONCAT(first_name,' ',last_name) LIKE ?", ["%{$search}%"]);
                    });
            });
        }

        $staffMembers = $staffQuery->get();

        $rows = [];
        $no = 1;

        foreach ($staffMembers as $staff) {

            $attendance = $attendanceRecords->get($staff->user_id);
            $user = $staff->user;
            $userId = $user->id ?? null;

            $staffId = $staff->id ?? null;
            $attendanceMonth = Carbon::parse($date)->format('m');
            $attendanceYear = Carbon::parse($date)->format('Y');
            $payrollExists = Expense::where('staff_id', $staffId)
                ->where('month', $attendanceMonth)
                ->where('year', $attendanceYear)
                ->exists();

            /* ------------------------------------------------
                CLASSIFY LEAVE DETAILS (Reducing Duplicate Loop)
            -------------------------------------------------*/
            $adminHalves = [];
            $attnHalves = [];

            foreach ($staff->leave as $leave) {
                foreach ($leave->leave_detail as $detail) {

                    $isAttendanceCreated = $attendance && $attendance->leave_detail_id == $detail->id;

                    if ($isAttendanceCreated) {
                        $attnHalves[] = $detail->type;
                    } else {
                        $adminHalves[] = $detail->type;
                    }
                }
            }

            $adminHasFull = in_array('Full', $adminHalves);
            $adminFirst = in_array('First Half', $adminHalves);
            $adminSecond = in_array('Second Half', $adminHalves);

            $attnHasFull = in_array('Full', $attnHalves);
            $attnFirst = in_array('First Half', $attnHalves);
            $attnSecond = in_array('Second Half', $attnHalves);

            /* -------------------------
               CALCULATE LEAVE PRIORITY
            --------------------------*/
            $leaveType =
                $adminHasFull ? 'Full' :
                ($attnHasFull ? 'Full' :
                    ($adminFirst ? 'First Half' :
                        ($adminSecond ? 'Second Half' :
                            ($attnFirst ? 'First Half' :
                                ($attnSecond ? 'Second Half' : null)))));

            $isAdminLeave = !empty($adminHalves);
            $isAttendanceLeave = !empty($attnHalves);

            /* -------------------------
                ALREADY MARKED ATTENDANCE
            --------------------------*/
            if ($attendance) {
                $rows[] = [
                    'id' => $attendance->id,
                    'no' => $no++,
                    'staff_id' => $attendance->staff_id,
                    'status' => $attendance->type,

                    'user' => [
                        'full_name' => $user,
                        'staff' => [
                            'id' => $attendance->user->staff->id ?? '',
                            'user_id' => $attendance->user->staff->user_id ?? '',
                        ],
                    ],

                    'type' => [
                        'id' => $userId,
                        'name' => $user,
                        'date' => Carbon::parse($date)->format('l, F j, Y'),
                        'status' => 'Update',
                        'type' => $attendance->type,
                        'reason' => $attendance->reason,
                    ],

                    'leave_type' => $leaveType,
                    'admin_leave' => $isAdminLeave,
                    'attendance_leave' => $isAttendanceLeave,
                    'holiday_days' => $holiday_days,
                    'holiday' => $holidays ?? false,
                    'day_name' => Carbon::parse($date)->format('l'),
                    'date' => $date,
                    'payroll_exists' => $payrollExists ?? false,
                ];

                continue;
            }

            /* -------------------------
                NOT MARKED ATTENDANCE
            --------------------------*/
            $rows[] = [
                'id' => '',
                'no' => $no++,
                'staff_id' => $staff->user_id,
                'status' => $leaveType === 'Full' ? 'Full Day Leave' : 'not marked',

                'user' => [
                    'full_name' => $user,
                    'staff' => [
                        'id' => $staff->id,
                        'user_id' => $staff->user_id ?? '',
                    ],
                ],

                'type' => [
                    'id' => $userId,
                    'name' => $user,
                    'date' => Carbon::parse($date)->format('l, F j, Y'),
                    'status' => 'Mark',
                ],

                'leave_type' => $leaveType,
                'admin_leave' => $isAdminLeave,
                'attendance_leave' => $isAttendanceLeave,
                'holiday' => $holidays ?? false,
                'holiday_days' => $holiday_days,
                'day_name' => Carbon::parse($date)->format('l'),
                'date' => $date,
                'payroll_exists' => $payrollExists ?? false,
            ];
        }

        return response()->json([
            'total' => count($rows),
            'rows' => $rows,
        ]);
    }

    public function attendance_show(Request $request)
    {
        ResponseService::noFeatureThenRedirect('Staff Attendance Management');
        ResponseService::noAnyPermissionThenRedirect(['staff-attendance-list']);

        $offset = request('offset', 0);
        $limit = request('limit');
        $sort = request('sort', 'staff_id');
        $order = request('order', 'ASC');
        $search = request('search');
        $attendanceType = request('attendance_type');

        $date = date('Y-m-d', strtotime(request('date')));

        $validator = Validator::make($request->all(), ['date' => 'required']);
        if ($validator->fails()) {
            ResponseService::errorResponse($validator->errors()->first());
        }

        $sessionYear = $this->cache->getDefaultSessionYear();

        $sql = $this->staffAttendance->builder()->where(['date' => $date, 'session_year_id' => $sessionYear->id])->with('user.staff');

        if ($attendanceType != null) {
            $sql = $sql->where('type', $attendanceType);
        }

        if ($search) {
            $sql = $sql->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orwhereRaw("concat(users.first_name,' ',users.last_name) LIKE '%" . $search . "%'")
                    ->orwhere('id', 'like', '%' . $search . '%');
            });
        }

        $total = $sql->count();
        $sql = $sql->orderBy($sort, $order);

        if ($limit) {
            if ($offset >= $total && $total > 0) {
                $lastPage = floor(($total - 1) / $limit) * $limit; // calculate last page offset
                $offset = $lastPage;
            }
            $sql = $sql->skip($offset)->take($limit);
        }

        $attendanceData = $sql->get();

        $no = 1;
        foreach ($attendanceData as $attendance) {
            $attendance->no = $no++;
        }

        $data = [
            'total' => $total,
            'rows' => $attendanceData
        ];

        return response()->json($data);
    }

    public function monthWiseIndex()
    {
        ResponseService::noFeatureThenRedirect('Staff Attendance Management');
        ResponseService::noAnyPermissionThenRedirect(['staff-attendance-list']);

        $sessionYears = SessionYear::pluck('name', 'id');
        return view('staff-attendance.month-wise', compact('sessionYears'));
    }

    public function monthWiseShow(Request $request, $user_id = null)
    {
        $limit = request('limit');
        $offset = request('offset', 0);
        $schoolSettings = $this->cache->getSchoolSettings();
        $month = $request->month;
        $sessionYearId = $request->session_year_id;

        $sql = $this->staff->builder()
            ->with('user')
            ->whereHas('staffAttendance', function ($q) use ($month, $sessionYearId) {
                $q->whereMonth('date', $month)
                    ->where('session_year_id', $sessionYearId);
            })
            ->orderBy('user_id', 'ASC');

        if ($user_id) {
            $sql->where('user_id', $user_id);
        }

        if ($request->search) {
            $search = $request->search;
            $sql->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                    ->orWhere('last_name', 'like', "%$search%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE '%$search%'");
            });
        }

        $total = $sql->count();

        if ($limit) {
            if ($offset >= $total && $total > 0) {
                $offset = floor(($total - 1) / $limit) * $limit;
            }
            $sql->skip($offset)->take($limit);
        }

        $res = $sql->get();

        /* ================= PRELOAD DATA ================= */

        $date = Carbon::create(null, $month, 1);

        // Month holidays (fixed dates)
        $holidayDates = Holiday::whereMonth('date', $month)
            ->pluck('date')
            ->toArray();

        // Weekly holidays
        $leaveMasterHoliday = $this->leaveMaster->builder()
            ->where('session_year_id', $sessionYearId)
            ->value('holiday');

        $holidayDays = $leaveMasterHoliday ? explode(',', $leaveMasterHoliday) : [];

        // Leaves
        $leavesQuery = $this->leave->builder()
            ->where('status', 1)
            ->with([
                'leave_detail' => function ($q) use ($month) {
                    $q->whereMonth('date', $month);
                }
            ]);

        if ($user_id) {
            $leavesQuery->where('user_id', $user_id);
        }

        $leaves = $leavesQuery->get();

        /* ===== BUILD LEAVE MAP (user_id + date) ===== */

        $leaveMap = [];

        foreach ($leaves as $leave) {
            foreach ($leave->leave_detail as $detail) {
                $leaveMap[$leave->user_id][$detail->date] = 'leave'; // 2 = Leave
            }
        }

        /* ================= BUILD ROWS ================= */

        $rows = [];

        foreach ($res as $row) {

            // Attendance indexed by date
            $attendanceByDate = $row->staffAttendance()
                ->whereMonth('date', $month)
                ->where('session_year_id', $sessionYearId)
                ->pluck('type', 'date');

            $staffAttendance = [
                'full_name' => $row->user->full_name,
                'user_id' => $row->user_id,
            ];

            for ($day = 1; $day <= $date->daysInMonth; $day++) {

                $currentDate = $date->copy()->day($day)->format('Y-m-d');
                $dayName = Carbon::parse($currentDate)->format('l');

                // Attendance value (0 or 1 are VALID)
                $attendanceValue = $attendanceByDate[$currentDate] ?? null;

                if (
                    in_array(
                        Carbon::createFromFormat('Y-m-d', $currentDate)->format($schoolSettings['date_format']),
                        $holidayDates
                    )
                ) {
                    $staffAttendance["day_$day"] = 3;
                }
                elseif (in_array($dayName, $holidayDays)) {
                    $staffAttendance["day_$day"] = 3;
                }
                else if ($attendanceValue !== null) {
                    $staffAttendance["day_$day"] = $attendanceValue;
                }
                elseif (isset($leaveMap[$row->user_id][$currentDate])) {
                    $staffAttendance["day_$day"] = 'leave';
                }
                else {
                    $staffAttendance["day_$day"] = null;
                }
            }

            $rows[] = $staffAttendance;
        }

        return response()->json([
            'total' => $total,
            'rows' => $rows,
            'leaves' => $leaves,
        ]);
    }

    public function yourIndex()
    {
        ResponseService::noFeatureThenRedirect('Staff Attendance Management');

        $sessionYears = SessionYear::pluck('name', 'id');
        $sessionYear = $this->cache->getDefaultSessionYear();

        return view('staff-attendance.your-index', compact('sessionYears', 'sessionYear'));
    }
}