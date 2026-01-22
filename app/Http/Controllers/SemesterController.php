<?php

namespace App\Http\Controllers;

use App\Repositories\Semester\SemesterInterface;
use App\Rules\uniqueForSchool;
use App\Services\BootstrapTableService;
use App\Services\CachingService;
use App\Services\ResponseService;
use App\Services\SessionYearsTrackingsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Throwable;
use Carbon\Carbon;

class SemesterController extends Controller
{
    private SemesterInterface $semester;
    private CachingService $cache;
    private SessionYearsTrackingsService $sessionYearsTrackingsService;

    public function __construct(SemesterInterface $semester, CachingService $cache, SessionYearsTrackingsService $sessionYearsTrackingsService)
    {
        $this->semester = $semester;
        $this->cache = $cache;
        $this->sessionYearsTrackingsService = $sessionYearsTrackingsService;
    }

    public function index()
    {
        // dd($this->semester->builder()->get()); // Temporary debug line
        ResponseService::noPermissionThenRedirect('semester-list');
        $defaultSessionYear = $this->cache->getDefaultSessionYear();
        return view('semester.index', compact('defaultSessionYear'));
    }

    public function store(Request $request)
    {
        ResponseService::noPermissionThenSendJson('semester-create');
        $request->validate([
            'name' => [
                'required',
                new uniqueForSchool('semesters', 'name')
            ],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        try {
            // Check if dates overlap with existing semesters
            $checkSemester = $this->checkIfDatesOverlap($request->start_date, $request->end_date);
            $schoolSettings = $this->cache->getSchoolSettings();
            if ($checkSemester['error']) {
                ResponseService::validationError($checkSemester['message'], $checkSemester['data']);
            }
            $defaultSessionYear = $this->cache->getDefaultSessionYear();

            // Add check: The start and end date of the request must be between the current semester's dates
            if ($defaultSessionYear) {
                $defaultStart = Carbon::createFromFormat($schoolSettings['date_format'], $defaultSessionYear->start_date);
                $defaultEnd = Carbon::createFromFormat($schoolSettings['date_format'], $defaultSessionYear->end_date);
                // dd($defaultStart, $defaultEnd);
                if (
                    Carbon::parse($request->start_date)->lt($defaultStart) || // Check if the start date is before the default start date
                    Carbon::parse($request->end_date)->gt($defaultEnd) // Check if the end date is after the default end date
                ) {
                    ResponseService::validationError(
                        'The semester start and end dates must be within the default session year\'s dates.',
                        [
                            'default_start_date' => $defaultStart->format('d-m-Y'),
                            'default_end_date' => $defaultEnd->format('d-m-Y')
                        ]
                    );
                }
            }
            // dd($request->all());
            $semester = $this->semester->create([
                'name' => $request->name,
                'start_date' => Carbon::createFromFormat('d-m-Y', $request->start_date)->format('Y-m-d'),
                'end_date' => Carbon::createFromFormat('d-m-Y', $request->end_date)->format('Y-m-d'),
                'school_id' => Auth::user()->school_id,
            ]);

            $sessionYear = $this->cache->getDefaultSessionYear();
            SessionYearsTrackingsService::storeSessionYearsTracking(get_class($semester), $semester->id, Auth::user()->id, $sessionYear->id, Auth::user()->school_id, null);
            ResponseService::successResponse('Data Stored Successfully');
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e, "Session Year Controller -> Store method");
            ResponseService::errorResponse();
        }
    }


    public function update($id, Request $request)
    {
        ResponseService::noPermissionThenSendJson('semester-edit');
        $request->validate([
            'name' => [
                'required',
                new uniqueForSchool('semesters', 'name', $id)
            ],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        try {
            // Check if dates overlap with existing semesters
            $checkSemester = $this->checkIfDatesOverlap($request->start_date, $request->end_date, $id);
            $schoolSettings = $this->cache->getSchoolSettings();
            if ($checkSemester['error']) {
                ResponseService::validationError($checkSemester['message'], $checkSemester['data']);
            }

            $defaultSessionYear = $this->cache->getDefaultSessionYear();

            // Add check: The start and end date of the request must be between the current session year's dates
            if ($defaultSessionYear) {
                $defaultStart = Carbon::createFromFormat($schoolSettings['date_format'], $defaultSessionYear->start_date);
                $defaultEnd = Carbon::createFromFormat($schoolSettings['date_format'], $defaultSessionYear->end_date);
                if (
                    Carbon::parse($request->start_date)->lt($defaultStart) || // Check if the start date is before the default start date
                    Carbon::parse($request->end_date)->gt($defaultEnd) // Check if the end date is after the default end date
                ) {
                    ResponseService::validationError(
                        'The semester start and end dates must be within the default session year\'s dates.',
                        [
                            'default_start_date' => $defaultStart->format('d-m-Y'),
                            'default_end_date' => $defaultEnd->format('d-m-Y')
                        ]
                    );
                }
            }

            $this->semester->update($id, [
                'name' => $request->name,
                'start_date' => Carbon::createFromFormat('d-m-Y', $request->start_date)->format('Y-m-d'),
                'end_date' => Carbon::createFromFormat('d-m-Y', $request->end_date)->format('Y-m-d'),
                'school_id' => Auth::user()->school_id,
            ]);

            $this->cache->removeSchoolCache(config('constants.CACHE.SCHOOL.SEMESTER'));

            ResponseService::successResponse('Data Updated Successfully');
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e, "Semester Controller -> Update method");
            ResponseService::errorResponse();
        }
    }

    public function show()
    {
        ResponseService::noPermissionThenRedirect('semester-list');
        $offset = request('offset', 0);
        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'ASC');
        $search = request('search');
        $showDeleted = request('show_deleted');

        $sql = $this->semester->builder()
            ->where(function ($q) use ($search) {
                $q->when($search, function ($query) use ($search) {
                    $query->where('id', 'LIKE', "%$search%")
                        ->orwhere('name', 'LIKE', "%$search%")
                        ->orwhere('start_date', 'LIKE', "%$search%")
                        ->orwhere('end_date', 'LIKE', "%$search%");
                });
            })
            ->when(!empty($showDeleted), function ($query) {
                $query->onlyTrashed();
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
            $operate = '';
            if ($showDeleted) {
                //Show Restore and Hard Delete Buttons
                $operate .= BootstrapTableService::restoreButton(route('semester.restore', $row->id));
                $operate .= BootstrapTableService::trashButton(route('semester.trash', $row->id));
            } else {
                //Show Edit and Soft Delete Buttons
                $operate .= BootstrapTableService::editButton(route('semester.update', $row->id));
                $operate .= BootstrapTableService::deleteButton(route('semester.destroy', $row->id));
            }
            $tempRow = $row->toArray();
            $tempRow['no'] = $no++;
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }


    public function destroy($id)
    {
        ResponseService::noPermissionThenSendJson('semester-delete');
        try {
            $semester = $this->cache->getDefaultSemesterData();

            if ($semester->id == $id) {
                ResponseService::errorResponse('Cannot delete the current semester');
            }
            $this->semester->deleteById($id);
            $sessionYear = $this->cache->getDefaultSessionYear();
            $this->sessionYearsTrackingsService->deleteSessionYearsTracking('App\Models\Semester', $id, Auth::user()->id, $sessionYear->id, Auth::user()->school_id, $semester->id);
            ResponseService::successResponse('Data Deleted Successfully');
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e, "Semester Controller -> Delete method", 'cannot_delete_because_data_is_associated_with_other_data');
            ResponseService::errorResponse();
        }
    }

    public function restore(int $id)
    {
        ResponseService::noPermissionThenSendJson('semester-delete');
        try {
            $this->semester->findOnlyTrashedById($id)->restore();
            ResponseService::successResponse("Data Restored Successfully");
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e);
            ResponseService::errorResponse();
        }
    }

    public function trash($id)
    {
        ResponseService::noPermissionThenSendJson('semester-delete');
        try {

            $semester = $this->semester->findOnlyTrashedById($id);
            if ($semester->current) {
                $this->cache->removeSchoolCache(config('constants.CACHE.SCHOOL.SEMESTER'));
            }
            $semester->forceDelete();
            ResponseService::successResponse("Data Deleted Permanently");
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e, "Semester Controller -> Trash Method", 'cannot_delete_because_data_is_associated_with_other_data');
            ResponseService::errorResponse();
        }
    }

    /**
     * Check if the given date range overlaps with any existing semester
     * 
     * @param string $startDate
     * @param string $endDate
     * @param int|null $ignoreID - Optional ID to ignore (for updates)
     * @return array
     */
    private function checkIfDatesOverlap(string $startDate, string $endDate, int $ignoreID = null)
    {
        $semesters = $this->semester->builder()->withTrashed();
        $schoolSettings = $this->cache->getSchoolSettings();

        if ($ignoreID !== null) {
            $semesters = $semesters->where('id', '!=', $ignoreID);
        }

        $semesters = $semesters->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->get();

        $newStartDate = \Carbon\Carbon::parse($startDate);
        $newEndDate = \Carbon\Carbon::parse($endDate);

        foreach ($semesters as $semester) {
            $existingStartDate = \Carbon\Carbon::parse(\Carbon\Carbon::createFromFormat($schoolSettings['date_format'], $semester->start_date));
            $existingEndDate = \Carbon\Carbon::parse(\Carbon\Carbon::createFromFormat($schoolSettings['date_format'], $semester->end_date));

            // Check for overlap: two date ranges overlap if:
            // (newStart <= existingEnd AND newEnd >= existingStart)
            if ($newStartDate->lte($existingEndDate) && $newEndDate->gte($existingStartDate)) {
                return [
                    'error' => true,
                    'message' => trans("The selected date range overlaps with existing semester: ") . $semester->name,
                    'data' => []
                ];
            }
        }

        return [
            'error' => false,
            'message' => 'success'
        ];
    }
}
