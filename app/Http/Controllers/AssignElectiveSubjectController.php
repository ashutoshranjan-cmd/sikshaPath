<?php

namespace App\Http\Controllers;

use App\Models\PaymentConfiguration;
use App\Repositories\Addon\AddonInterface;
use App\Repositories\AddonSubscription\AddonSubscriptionInterface;
use App\Repositories\Feature\FeatureInterface;
use App\Repositories\PaymentTransaction\PaymentTransactionInterface;
use App\Repositories\Subscription\SubscriptionInterface;
use App\Services\BootstrapTableService;
use App\Services\CachingService;
use App\Services\FeaturesService;
use App\Services\ResponseService;
use App\Services\SubscriptionService;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;

use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\Checkout\Session as StripeSession;

use App\Repositories\ClassSection\ClassSectionInterface;
use App\Repositories\Subject\SubjectInterface;
use App\Repositories\StudentSubject\StudentSubjectInterface;
use App\Repositories\Student\StudentInterface;
use App\Repositories\User\UserInterface;
use App\Repositories\SessionYear\SessionYearInterface;
use App\Repositories\ClassSchool\ClassSchoolInterface;
use App\Repositories\ClassSubject\ClassSubjectInterface;
use App\Models\ElectiveSubjectGroup;
use App\Models\ClassSection;

class AssignElectiveSubjectController extends Controller
{

    private ClassSectionInterface $classSection;
    private SubjectInterface $subject;
    private StudentSubjectInterface $studentSubject;
    private StudentInterface $student;
    private UserInterface $user;
    private ClassSchoolInterface $class;
    private SessionYearInterface $sessionYear;
    private ClassSubjectInterface $classSubject;
    public function __construct(
        ClassSectionInterface $classSection,
        SubjectInterface $subject,
        StudentSubjectInterface $studentSubject,
        StudentInterface $student,
        UserInterface $user,
        ClassSchoolInterface $class,
        SessionYearInterface $sessionYear,
        ClassSubjectInterface $classSubject
    ) {
        $this->classSection = $classSection;
        $this->subject = $subject;
        $this->studentSubject = $studentSubject;
        $this->classSubject = $classSubject;
        $this->student = $student;
        $this->user = $user;
        $this->sessionYear = $sessionYear;
        $this->class = $class;
    }

    public function index()
    {
        ResponseService::noPermissionThenRedirect('assign-elective-subject-list');
        try {
            $class_sections = $this->classSection->all(['*'], ['class', 'class.stream', 'section', 'medium']);
            $subjects = $this->subject->builder()->where('type', 'Elective')->get();

            // Get elective subject groups with proper data structure
            // Load the same way as show() function - load groups and then load subjects for each group
            $electiveSubjectGroups = $this->class->builder()
                ->with('elective_subject_groups')
                ->where('school_id', Auth::user()->school_id)
                ->get()
                ->map(function ($class) {
                    // Load subjects for each group (same relationship as defined in model)
                    if ($class->elective_subject_groups) {
                        $class->elective_subject_groups->each(function ($group) {
                            // Load subjects using the model's relationship definition
                            $group->load('subjects');
                        });
                    }
                    return $class;
                });

            $session_years = $this->sessionYear->all();

            return view('assign-elective-subject.index', compact('class_sections', 'session_years', 'electiveSubjectGroups'));
        } catch (\Exception $e) {
            return ResponseService::logErrorResponse($e, 'AssignElectiveSubjectController -> index method');
        }
    }

    public function store(Request $request)
    {
        ResponseService::noAnyPermissionThenSendJson(['assign-elective-subject-create']);

        try {

            $classSubjectIds = $request->class_subject_ids ?? [];

            // -------------------- VALIDATION --------------------
            $validator = Validator::make($request->all(), [
                'student_ids' => 'required|not_in:0',
                'class_section_id' => 'required',
                'session_year_id' => 'required',
                'class_subject_ids' => 'required|array|min:1',
                'class_subject_ids.*' => 'required|numeric'
            ], [
                'student_ids.required' => 'Please select at least one student.',
                'class_subject_ids.required' => 'Please select at least one subject.',
                'class_subject_ids.min' => 'Please select at least one subject.',
            ]);

            if ($validator->fails()) {
                return ResponseService::errorResponse($validator->errors()->first());
            }

            $studentIds = explode(",", $request->student_ids);
            $schoolId = Auth::user()->school_id;

            DB::beginTransaction();

            $electiveSubjects = $this->classSubject->builder()
                ->whereIn('id', $classSubjectIds)
                ->where('type', 'Elective')
                ->get();

            if ($electiveSubjects->count() !== count($classSubjectIds)) {
                return ResponseService::errorResponse('One or more invalid elective subjects selected.');
            }

            $electiveGroupIds = $electiveSubjects->pluck('elective_subject_group_id')->filter()->unique()->toArray();

            $oldClassSubjectIds = $this->classSubject->builder()
                ->whereIn('elective_subject_group_id', $electiveGroupIds)
                ->where('type', 'Elective')
                ->pluck('id')
                ->toArray();

            foreach ($studentIds as $studentId) {
                // Get the student record to access user_id
                $student = $this->student->builder()->where('id', $studentId)->first();

                if (!$student) {
                    continue; // Skip if student not found
                }

                $userId = $student->user_id; // student_id in student_subjects table is actually user_id

                // Remove old subjects from all groups that the new subjects belong to
                if (!empty($oldClassSubjectIds)) {
                    $this->studentSubject->builder()
                        ->where('student_id', $userId)
                        ->whereIn('class_subject_id', $oldClassSubjectIds)
                        ->where('class_section_id', $request->class_section_id)
                        ->where('session_year_id', $request->session_year_id)
                        ->where('school_id', $schoolId)
                        ->delete();
                }

                // Assign all new subjects
                foreach ($classSubjectIds as $classSubjectId) {
                    $this->studentSubject->create([
                        'student_id' => $userId, // Use user_id, not student table id
                        'class_subject_id' => $classSubjectId,
                        'class_section_id' => $request->class_section_id,
                        'session_year_id' => $request->session_year_id,
                        'school_id' => $schoolId
                    ]);
                }
            }

            DB::commit();

            ResponseService::successResponse("Elective Subjects Updated Successfully");
        } catch (\Exception $e) {
            DB::rollback();
            ResponseService::logErrorResponse($e, 'AssignElectiveSubjectController -> store');
            ResponseService::errorResponse();
        }
    }

    public function show()
    {
        ResponseService::noPermissionThenRedirect('assign-elective-subject-list');
        try {
            $offset = request('offset', 0);
            $limit = request('limit', 10);
            $sort = request('sort', 'id');
            $order = request('order', 'DESC');
            $search = request('search');
            $session_year_id = request('session_year_id');
            $class_section_id = request('class_section_id');
            $class_subject_id = request('class_subject_id');
            $status = request('status');
            $school_id = Auth::user()->school_id;

            $sql = $this->student->builder()
                ->where('application_status', 1)
                ->whereHas('class_section.class.elective_subject_groups')
                ->whereHas('user', function ($query) use ($school_id) {
                    $query->where('status', 1);
                })
                ->with([
                    'user:id,first_name,last_name,image,email',
                    'class_section.class.elective_subject_groups.subjects' => function ($query) {
                        $query->select('subjects.id', 'subjects.name', 'subjects.code', 'subjects.type')
                            ->withPivot('id as class_subject_id');
                    },
                    'class_section.class.stream',
                    'class_section.class.medium',
                    'class_section.class.shift',
                    'class_section.section',
                    'student_subjects' => function ($query) use ($school_id, $session_year_id) {
                        $query->where('school_id', $school_id)
                            ->where('session_year_id', $session_year_id)
                            ->with([
                                'class_subject' => function ($q) {
                                    $q->where('type', 'Elective')
                                        ->with(['subject', 'subjectGroup']);
                                }
                            ]);
                    },
                ]);

            // Search functionality
            if (!empty($search)) {
                $sql->where(function ($query) use ($search) {
                    $query->where('admission_no', 'LIKE', "%$search%")
                        ->orWhere('roll_number', 'LIKE', "%$search%")
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('first_name', 'LIKE', "%$search%")
                                ->orWhere('last_name', 'LIKE', "%$search%")
                                ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"]);
                        });
                });
            }

            // Class section filter
            if ($class_section_id) {
                $sql->whereHas('class_section', function ($query) use ($class_section_id) {
                    $query->where('id', $class_section_id);
                });
            }

            // Elective subject filter (class_subject_id)
            if ($class_subject_id && $class_subject_id !== 'data-not-found') {
                $sql->whereHas('student_subjects', function ($query) use ($class_subject_id, $school_id, $session_year_id) {
                    $query->where('class_subject_id', $class_subject_id)
                        ->where('school_id', $school_id)
                        ->where('session_year_id', $session_year_id);
                });
            }

            // Get all results first to calculate status and apply status filter
            // Note: We need to get all results to calculate status correctly
            if ($sort === 'full_name') {
                $sql->orderByRaw("CONCAT((SELECT first_name FROM users WHERE users.id = students.user_id), ' ', (SELECT last_name FROM users WHERE users.id = students.user_id)) $order");
            } else {
                $sql->orderBy($sort, $order);
            }
            $res = $sql->get();

            $rows = [];
            $no = 1;
            foreach ($res as $row) {
                $tempRow = $row->toArray();
                $tempRow['user_id'] = $row->user_id;
                $tempRow['user_image'] = $row->user ? $row->user->image : null;
                // Get only elective subjects
                $electiveSubjects = $row->student_subjects->filter(function ($subject) {
                    return $subject->class_subject && $subject->class_subject->type === 'Elective';
                });

                $tempRow['elective_subjects'] = $electiveSubjects->map(function ($subject) {
                    return $subject->class_subject->subject->name . ' (' . $subject->class_subject->subject->type . ')' ?? '';
                })->filter()->implode(', ');

                // Store assigned class_subject_ids for modal pre-checking
                $tempRow['assigned_class_subject_ids'] = $electiveSubjects->pluck('class_subject_id')->toArray();

                // Calculate status: Not Assigned, Incomplete, or Complete
                // Check if student has elective groups that require selection
                $hasElectiveGroups = false;
                $totalRequired = 0;
                $totalSelected = 0;

                if ($row->class_section && $row->class_section->class && $row->class_section->class->elective_subject_groups) {
                    $hasElectiveGroups = $row->class_section->class->elective_subject_groups->count() > 0;

                    // Calculate total required and selected subjects
                    foreach ($row->class_section->class->elective_subject_groups as $group) {
                        $totalRequired += $group->total_selectable_subjects ?? 0;

                        // Count how many subjects from this group are assigned
                        $groupSubjectIds = [];
                        if ($group->subjects) {
                            foreach ($group->subjects as $groupSubject) {
                                $classSubjectId = null;
                                if ($groupSubject->pivot && isset($groupSubject->pivot->class_subject_id)) {
                                    $classSubjectId = $groupSubject->pivot->class_subject_id;
                                } elseif (isset($groupSubject->class_subject_id)) {
                                    $classSubjectId = $groupSubject->class_subject_id;
                                }
                                if ($classSubjectId) {
                                    $groupSubjectIds[] = $classSubjectId;
                                }
                            }
                        }

                        // Count assigned subjects from this group
                        $assignedFromGroup = $electiveSubjects->filter(function ($subject) use ($groupSubjectIds) {
                            return in_array($subject->class_subject_id, $groupSubjectIds);
                        })->count();

                        $totalSelected += min($assignedFromGroup, $group->total_selectable_subjects ?? 0);
                    }
                }

                // Determine status
                if ($electiveSubjects->count() == 0) {
                    $tempRow['status'] = 'not_assigned';
                } elseif ($hasElectiveGroups && $totalRequired > 0 && $totalSelected < $totalRequired) {
                    $tempRow['status'] = 'incomplete';
                } else {
                    $tempRow['status'] = 'complete';
                }

                // Apply status filter if provided
                if ($status && $tempRow['status'] !== $status) {
                    continue; // Skip this row if it doesn't match the status filter
                }

                $operate = BootstrapTableService::editButton(route('assign.elective.subject.update', $row->id));
                $tempRow['operate'] = $operate;

                $rows[] = $tempRow;
            }

            // Apply pagination after filtering
            $total = count($rows);
            if ($offset >= $total && $total > 0) {
                $lastPage = floor(($total - 1) / $limit) * $limit;
                $offset = $lastPage;
            }
            $paginatedRows = array_slice($rows, $offset, $limit);

            // Update row numbers for paginated results
            foreach ($paginatedRows as $index => $row) {
                $paginatedRows[$index]['no'] = $offset + $index + 1;
            }

            return response()->json([
                'total' => $total,
                'rows' => $paginatedRows
            ]);
        } catch (\Exception $e) {
            return ResponseService::logErrorResponse($e, 'AssignElectiveSubjectController -> show method');
        }
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        ResponseService::noPermissionThenSendJson('assign-elective-subject-edit');
        ResponseService::successResponse('Data Updated Successfully');
    }

    public function destroy($id)
    {
        ResponseService::noPermissionThenSendJson('assign-elective-subject-delete');
        try {
            $studentSubject = $this->studentSubject->findOrFail($id);

            if ($studentSubject->school_id != Auth::user()->school_id) {
                throw new \Exception(__('Invalid Assignment'));
            }

            $studentSubject->delete();
            ResponseService::successResponse('Data Deleted Successfully');
        } catch (\Exception $e) {
            ResponseService::logErrorResponse($e, 'AssignElectiveSubjectController -> destroy method');
            ResponseService::errorResponse();
        }
    }

    public function restore($id)
    {
        ResponseService::noPermissionThenSendJson('assign-elective-subject-edit');
        ResponseService::successResponse('Data Restored Successfully');
    }

    public function status($id)
    {
        ResponseService::noAnyPermissionThenSendJson(['assign-elective-subject-create', 'assign-elective-subject-edit']);
        try {
            DB::beginTransaction();
            $addon = $this->addon->findById($id);
            $addon = ['status' => $addon->status == 1 ? 0 : 1];
            $this->addon->update($id, $addon);
            DB::commit();
            ResponseService::successResponse('Data Updated Successfully');
        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e, 'AssignElectiveSubjectController -> status method');
            ResponseService::errorResponse();
        }
    }

    public function removeSubject(Request $request)
    {
        try {
            // dd($request->all());


            $studentSubject = $this->studentSubject->builder()->where('student_id', $request->student_id)->where('class_subject_id', $request->class_subject_id);

            $studentSubject->delete();
            ResponseService::successResponse('Data Deleted Successfully');
        } catch (\Exception $e) {
            ResponseService::logErrorResponse($e, 'AssignElectiveSubjectController -> removeSubject method');
            ResponseService::errorResponse();
        }
    }
}
