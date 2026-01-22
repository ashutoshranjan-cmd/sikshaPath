<?php

namespace App\Http\Controllers;

use App\Models\ClassSchool;
use App\Models\ClassSection;
use App\Models\OnlineExamCommon;
use App\Repositories\ClassSection\ClassSectionInterface;
use App\Repositories\ClassSubject\ClassSubjectInterface;
use App\Repositories\OnlineExam\OnlineExamInterface;
use App\Repositories\OnlineExamCommon\OnlineExamCommonInterface;
use App\Repositories\OnlineExamQuestion\OnlineExamQuestionInterface;
use App\Repositories\OnlineExamQuestionChoice\OnlineExamQuestionChoiceInterface;
use App\Repositories\OnlineExamQuestionCommon\OnlineExamQuestionCommonInterface;
use App\Repositories\OnlineExamQuestionOption\OnlineExamQuestionOptionInterface;
use App\Repositories\OnlineExamStudentAnswer\OnlineExamStudentAnswerInterface;
use App\Repositories\SessionYear\SessionYearInterface;
use App\Repositories\Student\StudentInterface;
use App\Repositories\StudentOnlineExamStatus\StudentOnlineExamStatusInterface;
use App\Repositories\Subject\SubjectInterface;
use App\Repositories\SubjectTeacher\SubjectTeacherInterface;
use App\Services\BootstrapTableService;
use App\Services\CachingService;
use App\Services\ResponseService;
use App\Services\SessionYearsTrackingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OnlineExamController extends Controller
{
    private ClassSectionInterface $classSection;
    private SubjectTeacherInterface $subjectTeacher;
    private OnlineExamInterface $onlineExam;
    private OnlineExamQuestionChoiceInterface $onlineExamQuestionChoice;
    private OnlineExamQuestionInterface $onlineExamQuestion;
    private OnlineExamQuestionOptionInterface $onlineExamQuestionOption;
    private OnlineExamStudentAnswerInterface $onlineExamStudentAnswer;
    private CachingService $cache;
    private StudentInterface $student;
    private StudentOnlineExamStatusInterface $studentOnlineExamStatus;
    private ClassSubjectInterface $classSubjects;
    private SessionYearInterface $sessionYear;
    private OnlineExamCommonInterface $onlineExamCommon;
    private SessionYearsTrackingsService $sessionYearsTrackingsService;
    private SubjectInterface $subject;

    public function __construct(ClassSectionInterface $classSection, SubjectTeacherInterface $subjectTeacher, OnlineExamInterface $onlineExam, OnlineExamQuestionChoiceInterface $onlineExamQuestionChoice, OnlineExamQuestionInterface $onlineExamQuestion, OnlineExamQuestionOptionInterface $onlineExamQuestionOption, OnlineExamStudentAnswerInterface $onlineExamStudentAnswer, CachingService $cachingService, StudentInterface $student, StudentOnlineExamStatusInterface $studentOnlineExamStatus, ClassSubjectInterface $classSubjects, SessionYearInterface $sessionYear, OnlineExamCommonInterface $onlineExamCommon, SessionYearsTrackingsService $sessionYearsTrackingsService, SubjectInterface $subject)
    {
        $this->classSection = $classSection;
        $this->subjectTeacher = $subjectTeacher;
        $this->onlineExam = $onlineExam;
        $this->onlineExamQuestionChoice = $onlineExamQuestionChoice;
        $this->onlineExamQuestion = $onlineExamQuestion;
        $this->onlineExamQuestionOption = $onlineExamQuestionOption;
        $this->onlineExamStudentAnswer = $onlineExamStudentAnswer;
        $this->cache = $cachingService;
        $this->student = $student;
        $this->studentOnlineExamStatus = $studentOnlineExamStatus;
        $this->classSubjects = $classSubjects;
        $this->sessionYear = $sessionYear;
        $this->onlineExamCommon = $onlineExamCommon;
        $this->sessionYearsTrackingsService = $sessionYearsTrackingsService;
        $this->subject = $subject;
    }

    public function index()
    {
        ResponseService::noFeatureThenRedirect('Exam Management');
        ResponseService::noPermissionThenRedirect('online-exam-list');

        // Get classes for the initial dropdown
        $classes = $this->getClassesForUser();

        $sessionYear = $this->sessionYear->builder()->pluck('name', 'id');
        $defaultSessionYear = $this->cache->getDefaultSessionYear();
        $rand_key = random_int(100000, 999999);

        return response(view('online_exam.index', compact('classes', 'sessionYear', 'defaultSessionYear', 'rand_key')));
    }

    /**
     * Get classes based on user role
     */
    public function getClassesForUser()
    {
        if (Auth::user()->hasRole('Teacher')) {
            // Teachers can only see classes where they are teaching subjects
            return $this->classSection->builder()
                ->whereHas('subject_teachers', function ($query) {
                    $query->where('teacher_id', Auth::user()->id);
                })
                ->with(['class.medium', 'class.stream', 'section'])
                ->get()
                ->groupBy('class.id')
                ->map(function ($classSections) {
                    $class = $classSections->first()->class;
                    $mediumName = $class->medium ? $class->medium->name : '';
                    $streamName = $class->stream ? $class->stream->name : '';

                    return [
                        'id' => $class->id,
                        'name' => $class->name,
                        'medium' => $mediumName,
                        'stream' => $streamName,
                        'full_name' => $class->name . ' - ' . $mediumName . ($streamName ? ' (' . $streamName . ')' : '')
                    ];
                })
                ->values();
        } else {
            // School Admin can see all classes
            return $this->classSection->builder()
                ->with(['class.medium', 'class.stream', 'section'])
                ->get()
                ->groupBy('class.id')
                ->map(function ($classSections) {
                    $class = $classSections->first()->class;
                    $mediumName = $class->medium ? $class->medium->name : '';
                    $streamName = $class->stream ? $class->stream->name : '';

                    return [
                        'id' => $class->id,
                        'name' => $class->name,
                        'medium' => $mediumName,
                        'stream' => $streamName,
                        'full_name' => $class->name . ' - ' . $mediumName . ($streamName ? ' (' . $streamName . ')' : '')
                    ];
                })
                ->values();
        }
    }

    /**
     * Get sections for a specific class
     */
    public function getSectionsByClass(Request $request)
    {
        $classId = $request->class_id;

        if (!$classId) {
            return response()->json(['error' => 'Class ID is required'], 400);
        }

        if (Auth::user()->hasRole('Teacher')) {
            // Teachers can only see sections where they are teaching subjects
            $sections = $this->classSection->builder()
                ->where('class_id', $classId)
                ->whereHas('subject_teachers', function ($q) {
                    $q->where('teacher_id', Auth::user()->id);
                })
                ->with('section')
                ->get()
                ->map(function ($classSection) {
                    return [
                        'id' => $classSection->id,
                        'name' => $classSection->section ? $classSection->section->name : '',
                        'full_name' => $classSection->section ? $classSection->section->name : ''
                    ];
                })
                ->filter(function ($section) {
                    return !empty($section['name']);
                })
                ->values();
        } else {
            // School Admin can see all sections for the class
            $sections = $this->classSection->builder()
                ->where('class_id', $classId)
                ->with('section')
                ->get()
                ->map(function ($classSection) {
                    return [
                        'id' => $classSection->id,
                        'name' => $classSection->section ? $classSection->section->name : '',
                        'full_name' => $classSection->section ? $classSection->section->name : ''
                    ];
                })
                ->filter(function ($section) {
                    return !empty($section['name']);
                })
                ->values();
        }

        return response()->json($sections);
    }

    /**
     * Get subjects for a specific class section
     */
    public function getSubjectsByClassSection(Request $request)
    {
        $classSectionIds = $request->class_section_id;

        if (!$classSectionIds) {
            return response()->json(['error' => 'Class Section ID is required'], 400);
        }

        // Ensure we have an array
        if (!is_array($classSectionIds)) {
            $classSectionIds = [$classSectionIds];
        }

        if (Auth::user()->hasRole('Teacher')) {
            // Teachers can only see subjects they are assigned to
            $subjects = $this->subjectTeacher->builder()
                ->whereIn('class_section_id', $classSectionIds)
                ->where('teacher_id', Auth::user()->id)
                ->with(['subject', 'class_subject'])
                ->get()
                ->unique('class_subject_id')
                ->map(function ($subjectTeacher) {
                    return [
                        'id' => $subjectTeacher->class_subject_id,
                        'subject_id' => $subjectTeacher->subject_id,
                        'subject_name' => $subjectTeacher->subject->name,
                        'subject_type' => $subjectTeacher->class_subject->type ?? 'Compulsory',
                        'subject_with_name' => $subjectTeacher->subject_with_name
                    ];
                });
        } else {
            // School Admin can see all subjects for the class sections
            // Get the class_id from the first class section
            $firstClassSection = $this->classSection->findById($classSectionIds[0]);
            $subjects = $this->classSubjects->builder()
                ->where('class_id', $firstClassSection->class_id)
                ->with('subject')
                ->get()
                ->map(function ($classSubject) {
                    return [
                        'id' => $classSubject->id,
                        'subject_id' => $classSubject->subject_id,
                        'subject_name' => $classSubject->subject->name,
                        'subject_type' => $classSubject->type,
                        'subject_with_name' => $classSubject->subject_with_name
                    ];
                });
        }

        return response()->json($subjects);
    }

    /**
     * Validate that the user has access to the selected class, sections, and subject
     */
    private function validateUserAccess(Request $request)
    {
        $classId = $request->class_id;
        $classSectionIds = $request->class_section_id;
        $subjectId = $request->subject_id;

        if (Auth::user()->hasRole('Teacher')) {
            // Validate class access - only where teacher is teaching subjects
            $hasClassAccess = $this->classSection->builder()
                ->where('class_id', $classId)
                ->whereHas('subject_teachers', function ($q) {
                    $q->where('teacher_id', Auth::user()->id);
                })
                ->exists();

            if (!$hasClassAccess) {
                throw new \Exception('You do not have access to this class.');
            }

            // Validate section access - only where teacher is teaching subjects
            foreach ($classSectionIds as $sectionId) {
                $hasSectionAccess = $this->classSection->builder()
                    ->where('id', $sectionId)
                    ->where('class_id', $classId)
                    ->whereHas('subject_teachers', function ($q) {
                        $q->where('teacher_id', Auth::user()->id);
                    })
                    ->exists();

                if (!$hasSectionAccess) {
                    throw new \Exception('You do not have access to one or more selected sections.');
                }
            }
            // dd($classSectionIds);
            // Validate subject access for each section
            foreach ($classSectionIds as $sectionId) {

                $hasSubjectAccess = $this->subjectTeacher->builder()
                    ->where('class_section_id', $sectionId)
                    ->where('subject_id', $subjectId)
                    ->where('teacher_id', Auth::user()->id)
                    ->exists();
                if (!$hasSubjectAccess) {
                    throw new \Exception('You do nothave access to this subject for one or more selected sections.');
                }
            
            }
        }
        // School Admin has access to everything, so no additional validation needed
    }

    public function store(Request $request)
    {
        ResponseService::noFeatureThenRedirect('Exam Management');
        ResponseService::noPermissionThenRedirect('online-exam-create');

        $request->validate([
            'class_id' => 'required|numeric',
            'subject_id' => 'required|numeric',
            'title' => 'required',
            'exam_key' => 'required|unique:online_exams,exam_key,NULL,id,school_id,' . Auth::user()->school_id,
            'duration' => 'required|numeric|gte:1',
            'start_date' => 'required',
            'end_date' => 'required|after:start_date',
        ]);

        $classSection = ClassSection::where('class_id', $request->class_id)->first();
        if (empty($request->class_section_id)) {
            if ($classSection && $classSection->section_id !== null) {
                ResponseService::errorResponse('Class Section is Required');
            }
        }

        if (empty($request->class_section_id) || (is_array($request->class_section_id) && count(array_filter($request->class_section_id)) == 0)) {
            $classSectionIds = [$classSection->id];
        } else {
            // Ensure it's an array
            $classSectionIds = is_array($request->class_section_id) ? $request->class_section_id : [$request->class_section_id];
        }

        $request->merge(['class_section_id' => $classSectionIds]);

        $this->validateUserAccess($request);

        try {
            DB::beginTransaction();
            $sessionYear = $this->cache->getDefaultSessionYear();

            // Get classSectionIds from request (already set above)
            $classSectionIds = $request->class_section_id;

            $onlineExamList = [];
            foreach ($classSectionIds as $section_id) {
                $onlineExamList = array_merge($request->all(), ['class_section_id' => $section_id]);
            }

            // Get the related class subject for each section
            if ($classSectionIds) {
                foreach ($classSectionIds as $section_id) {
                    if (Auth::user()->hasRole('Teacher')) {
                        $SubjectTeacher = $this->subjectTeacher->builder()->where('class_section_id', $section_id)->where('subject_id', $request->subject_id)->first();
                        $onlineExamList['exam_key'] = $request->exam_key;
                        $onlineExamList['class_subject_id'] = $SubjectTeacher->class_subject_id;
                        $onlineExamList['session_year_id'] = $sessionYear->id;
                    } else {
                        $classSection = $this->classSection->builder()->where('id', $section_id)->with([
                            'class_subject' => function ($q) use ($request) {
                                $q->where('subject_id', $request->subject_id);
                            }
                        ])->first();
                        // dd($classSection->toArray());
                        $onlineExamList['exam_key'] = $request->exam_key;
                        $onlineExamList['class_subject_id'] = $classSection->class_subject->id;
                        $onlineExamList['session_year_id'] = $sessionYear->id;
                    }
                }
            }

            unset($onlineExamList['subject_id']);
            unset($onlineExamList['class_id']);

            $onlineExam = $this->onlineExam->create($onlineExamList);

            $this->sessionYearsTrackingsService->storeSessionYearsTracking('App\Models\OnlineExam', $onlineExam->id, Auth::user()->id, $sessionYear->id, Auth::user()->school_id, null);

            $onlineExamCommonData = [];
            $onlineExamCommonData['online_exam_id'] = $onlineExam->id;

            // Create online_exam_common data for each section
            foreach ($classSectionIds as $section_id) {

                if (Auth::user()->hasRole('Teacher')) {
                    $subjectTeacher = $this->subjectTeacher->builder()->where('class_section_id', $section_id)->where('subject_id', $request->subject_id)->first();

                    $onlineExamCommonData['class_section_id'] = $section_id;
                    $onlineExamCommonData['class_subject_id'] = $subjectTeacher->class_subject_id;
                    $this->onlineExamCommon->create($onlineExamCommonData);
                } else {
                    $classSection = $this->classSection->builder()->where('id', $section_id)->with([
                        'class_subject' => function ($q) use ($request) {
                            $q->where('subject_id', $request->subject_id);
                        }
                    ])->first();
                    $onlineExamCommonData['class_section_id'] = $section_id;
                    $onlineExamCommonData['class_subject_id'] = $classSection->class_subject->id;
                    $this->onlineExamCommon->create($onlineExamCommonData);
                }


                $this->sessionYearsTrackingsService->storeSessionYearsTracking('App\Models\OnlineExamCommon', $onlineExamCommonData['class_section_id'], Auth::user()->id, $sessionYear->id, Auth::user()->school_id, null);
            }

            $userIds = DB::table('students')
                ->where('class_section_id', $request->class_section_id)
                ->where(function ($query) use ($request) {
                    $query->whereExists(function ($sub) use ($request) {
                        $sub->select(DB::raw(1))
                            ->from('student_subjects')
                            ->join('class_subjects', 'student_subjects.class_subject_id', '=', 'class_subjects.id')
                            ->whereColumn('student_subjects.student_id', 'students.user_id')
                            ->where('student_subjects.class_section_id', $request->class_section_id)
                            ->where('class_subjects.subject_id', $request->subject_id)
                            ->where('class_subjects.type', 'Elective');
                    })->orWhereExists(function ($sub) use ($request) {
                        $sub->select(DB::raw(1))
                            ->from('class_sections')
                            ->join('class_subjects', 'class_sections.class_id', '=', 'class_subjects.class_id')
                            ->whereColumn('class_sections.id', 'students.class_section_id')
                            ->where('class_sections.id', $request->class_section_id)
                            ->where('class_subjects.subject_id', $request->subject_id)
                            ->where('class_subjects.type', 'Compulsory');
                    });
                })
                ->pluck('user_id')
                ->toArray();
            // $subjectName = $this->subject->builder()->where('id', $request->subject_id)->first();
            // send_notification($userIds, 'Online Exam Alert !!!', 'New online exam added for ' . $subjectName->name . '(' . $subjectName->type . ')', 'exam');

            DB::commit();
            ResponseService::successResponse('Data Stored Successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e, 'OnlineExamController@store', 'store');
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            if (
                Str::contains($e->getMessage(), [
                    'does not exist',
                    'file_get_contents'
                ])
            ) {
                DB::commit();
                ResponseService::warningResponse("Data Stored successfully. But App push notification not send.");
            } else {
                DB::rollback();
                ResponseService::logErrorResponse($e, "Online Exam Controller -> Store method");
                ResponseService::errorResponse();
            }
        }
    }


    public function show()
    {
        ResponseService::noFeatureThenRedirect('Exam Management');
        ResponseService::noPermissionThenSendJson('online-exam-list');

        $offset = request('offset', 0);
        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'ASC');
        $search = request('search');
        $showDeleted = request('show_deleted');
        $session_year_id = request('session_year_id');
        $exam_status = request('exam_status');

        // BASE QUERY: OnlineExamCommon (correct)
        $sql = $this->onlineExamCommon->builder()
            ->with([
                'online_exam' => function ($q) {
                    $q->with([
                        'class_subject.subject',
                        'question_choice',
                        'student_attempt' => function ($query) {
                            $query->with(['student_data.student']);
                        }
                    ]);
                },
                'class_section.class',
                'class_section.section',
                'class_section.medium',
                'class_section.students' => function ($q) {
                    $q->whereHas('user', function ($q) {
                        $q->where('status', 1);
                    });
                }
            ])
            ->when(request('class_section_id'), function ($q) {
                $q->where('class_section_id', request('class_section_id'));
            })
            ->when(request('class_subject_id'), function ($query) {
                $query->where('class_subject_id', request('class_subject_id'));
            })
            ->when(request('class_id'), function ($query) {
                $class_id = request('class_id');
                $query->whereHas('class_section', function ($q) use ($class_id) {
                    $q->where('class_id', $class_id);
                });
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('id', 'LIKE', "%$search%")
                        ->orWhereHas('online_exam', function ($q) use ($search) {
                            $q->where('title', 'LIKE', "%$search%")
                                ->orWhere('exam_key', 'LIKE', "%$search%")
                                ->orWhere('duration', 'LIKE', "%$search%")
                                ->orWhere('start_date', 'LIKE', "%" . date('Y-m-d H:i:s', strtotime($search)) . "%")
                                ->orWhere('end_date', 'LIKE', "%" . date('Y-m-d H:i:s', strtotime($search)) . "%");
                        })
                        ->orWhereHas('online_exam.class_subject.subject', function ($query) use ($search) {
                            $query->where('name', 'LIKE', "%$search%")
                                ->orWhere('type', 'LIKE', "%$search%");
                        });
                });
            })
            ->when(!empty($showDeleted), function ($q) {
                $q->whereHas('online_exam', function ($query) {
                    $query->onlyTrashed();
                });
            })
            ->when($session_year_id, function ($query) use ($session_year_id) {
                $query->whereHas('online_exam', function ($q) use ($session_year_id) {
                    $q->where('session_year_id', $session_year_id);
                });
            })
            ->when($exam_status, function ($query) use ($exam_status) {
                $now = Carbon::now();
                $query->whereHas('online_exam', function ($q) use ($exam_status, $now) {
                    if ($exam_status === 'Upcoming') {
                        $q->whereRaw('start_date > ?', [$now]);
                    } elseif ($exam_status === 'On Going') {
                        $q->whereRaw('start_date <= ?', [$now])
                            ->whereRaw('end_date >= ?', [$now]);
                    } elseif ($exam_status === 'Completed') {
                        $q->whereRaw('end_date < ?', [$now]);
                    }
                });
            });

        // Pagination
        $totalExams = $sql->count();
        if ($offset >= $totalExams && $totalExams > 0) {
            $offset = floor(($totalExams - 1) / $limit) * $limit;
        }

        $res = $sql->orderBy($sort, $order)->skip($offset)->take($limit)->get();
        $actualTotal = $totalExams;

        // Get all exam IDs from the results to check for linked exams
        $examIds = $res->pluck('online_exam_id')->unique();

        // Count commons for each exam to determine if linked
        $examCommonsCount = [];
        foreach ($examIds as $examId) {
            $examCommonsCount[$examId] = $this->onlineExamCommon->builder()
                ->where('online_exam_id', $examId)
                ->count();
        }

        // Colors
        $colorPalette = [
            '#8B5CF6',
            '#EC4899',
            '#3B82F6',
            '#10B981',
            '#F59E0B',
            '#EF4444',
            '#06B6D4',
            '#8B5CF6',
            '#A855F7',
            '#6366F1'
        ];
        $linkedExamColors = [];
        foreach ($examIds as $examId) {
            if ($examCommonsCount[$examId] > 1) {
                $linkedExamColors[$examId] = $colorPalette[$examId % count($colorPalette)];
            }
        }

        $rows = [];
        $no = $offset + 1;

        // Track which exam_ids we've already seen (to identify first occurrence)
        $seenExamIds = [];

        foreach ($res as $row) {
            // FIX: row itself IS online_exam_common
            $common = $row;
            $operate = '';

            // Check if this exam is linked (has multiple commons)
            $isLinked = ($examCommonsCount[$row->online_exam_id] ?? 0) > 1;
            $linkedColor = $isLinked ? ($linkedExamColors[$row->online_exam_id] ?? '#8B5CF6') : null;

            // Check if this is the first occurrence of this exam in the current page
            $isFirstOccurrence = !isset($seenExamIds[$row->online_exam_id]);
            if ($isFirstOccurrence) {
                $seenExamIds[$row->online_exam_id] = true;
            }

            // Class Section Name
            $classSectionDisplay = '';
            if ($common->class_section) {
                if (
                    $common->class_section->relationLoaded('class') &&
                    $common->class_section->relationLoaded('section') &&
                    $common->class_section->relationLoaded('medium')
                ) {
                    $classSectionDisplay = $common->class_section->full_name;
                } else {
                    $name = '';
                    if ($common->class_section->relationLoaded('class') && $common->class_section->class) {
                        $name = $common->class_section->class->name;
                    }
                    if ($common->class_section->relationLoaded('section') && $common->class_section->section) {
                        $name .= ' - ' . $common->class_section->section->name;
                    }
                    if ($common->class_section->relationLoaded('medium') && $common->class_section->medium) {
                        $name .= ' - ' . $common->class_section->medium->name;
                    }
                    $classSectionDisplay = $name ?: '';
                }
            }

            // Students
            $totalStudentsForSection = 0;
            if ($common->class_section && $common->class_section->relationLoaded('students')) {
                $totalStudentsForSection = $common->class_section->students->count();
            }

            // Get student attempts for this specific class section
            $studentAttemptedForSection = 0;
            if ($common->class_section_id && $row->online_exam) {
                // Use the relationship if loaded, otherwise query directly
                if ($row->online_exam->relationLoaded('student_attempt')) {
                    $studentAttemptedForSection = $row->online_exam->student_attempt
                        ->filter(function ($attempt) use ($common) {
                            return $attempt->student_data &&
                                $attempt->student_data->student &&
                                $attempt->student_data->student->class_section_id == $common->class_section_id;
                        })
                        ->count();
                } else {
                    // Query directly if not loaded
                    $studentAttemptedForSection = $row->online_exam->student_attempt()
                        ->whereHas('student_data.student', function ($q) use ($common) {
                            $q->where('class_section_id', $common->class_section_id);
                        })
                        ->count();
                }
            }

            // Buttons - use online_exam_id, not common id
            $examId = $row->online_exam_id;
            if ($showDeleted) {
                $operate .= BootstrapTableService::menuRestoreButton('restore', route('online-exam.restore', $examId));
                $operate .= BootstrapTableService::menuTrashButton('delete', route('online-exam.trash', $examId));
            } else {
                if (Auth::user()->can('online-exam-result-list')) {
                    $operate .= BootstrapTableService::menuButton('Result', route('online-exam.result.index', ['id' => $examId]), [], []);
                }
                if (Auth::user()->can('online-exam-list')) {
                    $operate .= BootstrapTableService::menuButton('add_questions', route('online-exam.add.questions.index', $examId), [], []);
                    $operate .= BootstrapTableService::menuEditButton('edit', route('online-exam.update', $examId));
                    $operate .= BootstrapTableService::menuDeleteButton('delete', route('online-exam.destroy', $examId));
                }
            }

            // Dates - access from online_exam relationship
            $onlineExam = $row->online_exam;
            if (!$onlineExam) {
                continue; // Skip if exam not found
            }

            $start = Carbon::parse($onlineExam->getRawOriginal('start_date'));
            $end = Carbon::parse($onlineExam->getRawOriginal('end_date'));

            $tempRow = [];
            $tempRow['id'] = $onlineExam->id; // Use exam ID for the row
            $tempRow['no'] = $no++;
            $tempRow['class_section_with_medium'] = $classSectionDisplay;
            $tempRow['subject_name'] = $onlineExam->class_subject && $onlineExam->class_subject->relationLoaded('subject')
                ? $onlineExam->class_subject->subject_with_name
                : '';
            $tempRow['title'] = htmlspecialchars_decode($onlineExam->title);
            $tempRow['start_date'] = $start->format('Y-m-d H:i');
            $tempRow['start_date_db'] = $onlineExam->start_date;
            $tempRow['end_date'] = $end->format('Y-m-d H:i');
            $tempRow['end_date_db'] = $onlineExam->end_date;
            $tempRow['total_questions'] = $onlineExam->question_choice ? $onlineExam->question_choice->count() : 0;
            $tempRow['participants'] = $studentAttemptedForSection . '/' . $totalStudentsForSection;
            // Set is_linked = 1 only on the first row of each linked exam (formatter will display badge)
            $tempRow['is_linked'] = ($isLinked && $isFirstOccurrence) ? 1 : 0;
            $tempRow['linked_color'] = $linkedColor;
            $tempRow['created_at'] = $onlineExam->created_at;
            $tempRow['updated_at'] = $onlineExam->updated_at;
            $tempRow['exam_status_name'] = $onlineExam->exam_status_name ?? '';
            $tempRow['exam_key'] = $onlineExam->exam_key;
            $tempRow['duration'] = $onlineExam->duration;
            $tempRow['operate'] = BootstrapTableService::menuItem($operate);
            $rows[] = $tempRow;
        }

        return response()->json([
            'total' => $actualTotal,
            'rows' => $rows
        ]);
    }


    public function update(Request $request, $id)
    {
        ResponseService::noFeatureThenRedirect('Exam Management');
        ResponseService::noPermissionThenSendJson('online-exam-edit');
        $validator = Validator::make($request->all(), [
            'edit_title' => 'required',
            'edit_exam_key' => 'required|numeric',
            'edit_duration' => 'required|numeric|gte:1',
            'edit_start_date' => 'required|date',
            'edit_end_date' => 'required|date'
        ]);

        if ($validator->fails()) {
            ResponseService::errorResponse($validator->errors()->first());
        }
        try {
            DB::beginTransaction();
            $this->onlineExam->update($id, array(
                'title' => $request->edit_title,
                'exam_key' => $request->edit_exam_key,
                'duration' => $request->edit_duration,
                'start_date' => $request->edit_start_date,
                'end_date' => $request->edit_end_date,
            ));
            DB::commit();
            ResponseService::successResponse("Data Updated Successfully");
        } catch (Throwable $e) {
            DB::rollback();
            ResponseService::logErrorResponse($e, "Online Exam Controller -> Update method");
            ResponseService::errorResponse();
        }
    }

    public function destroy($id)
    {
        ResponseService::noFeatureThenRedirect('Exam Management');
        ResponseService::noPermissionThenSendJson('online-exam-delete');
        try {
            DB::beginTransaction();
            $this->onlineExam->deleteById($id);
            $sessionYear = $this->cache->getDefaultSessionYear();
            $this->sessionYearsTrackingsService->storeSessionYearsTracking('App\Models\OnlineExam', $id, Auth::user()->id, $sessionYear->id, Auth::user()->school_id, null);
            DB::commit();
            ResponseService::successResponse('Data Deleted Successfully');
        } catch (Throwable $e) {
            DB::rollback();
            ResponseService::logErrorResponse($e, "Online Exam Controller -> Delete method");
            ResponseService::errorResponse();
        }
    }

    public function restore(int $id)
    {
        ResponseService::noFeatureThenRedirect('Exam Management');
        ResponseService::noPermissionThenSendJson('online-exam-delete');
        try {
            $this->onlineExam->findOnlyTrashedById($id)->restore();
            ResponseService::successResponse("Data Restored Successfully");
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e);
            ResponseService::errorResponse();
        }
    }

    public function trash($id)
    {
        ResponseService::noFeatureThenRedirect('Exam Management');
        ResponseService::noPermissionThenSendJson('online-exam-delete');
        try {
            $this->onlineExam->findOnlyTrashedById($id)->forceDelete();
            ResponseService::successResponse("Data Deleted Permanently");
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e, "Online Exam Controller -> Trash Method", 'cannot_delete_because_data_is_associated_with_other_data');
            ResponseService::errorResponse();
        }
    }

    public function addQuestionIndex($id)
    {
        ResponseService::noFeatureThenRedirect('Exam Management');
        ResponseService::noPermissionThenRedirect('online-exam-questions-list');
        $onlineExam = $this->onlineExam->findById($id, ['*'], ['online_exam_commons.class_subject.subject']);
        $classSectionIds = $onlineExam->online_exam_commons->pluck('class_section_id');
        $classSubjectIds = $onlineExam->online_exam_commons->pluck('class_subject_id');

        // Get class_section with medium name
        $onlineExamCommons = $this->onlineExamCommon->builder()
            ->where('online_exam_id', $id)
            ->with('class_section.class.medium', 'class_section.section', 'class_section.medium')
            ->get();

        // dd($onlineExamCommons);
        $examQuestions = $this->onlineExamQuestionChoice->builder()->where('online_exam_id', $id)->with('online_exam', 'questions')->get();
        return response(view('online_exam.exam_questions', compact('onlineExam', 'examQuestions', 'onlineExamCommons', 'classSectionIds', 'classSubjectIds')));
    }

    public function storeExamQuestionChoices(Request $request)
    {
        ResponseService::noFeatureThenRedirect('Exam Management');
        ResponseService::noPermissionThenRedirect('online-exam-questions-create');
        $request->validate([
            'question' => 'required',
            'option_data.*.option' => 'required',
            'answer.*' => 'required',
            'image' => 'nullable|mimes:jpeg,png,jpg|image|max:3048',
        ]);

        try {
            DB::beginTransaction();

            $classSectionIds = is_array($request->class_section_id) ? $request->class_section_id : [$request->class_section_id];

            foreach ($classSectionIds as $classSectionId) {
                $classSection = $this->classSection->findById($classSectionId);
                $classId = $classSection->class_id;
            }
            $onlineExamQuestionData = array(
                'class_id' => $classId,
                'class_subject_id' => $request->class_subject_id,
                'question' => htmlspecialchars($request->question),
                'image_url' => $request->image,
                'note' => $request->note,
                'difficulty' => $request->difficulty,
                'last_edited_by' => Auth::user()->id,
            );
            $onlineExamQuestion = $this->onlineExamQuestion->create($onlineExamQuestionData);

            // foreach ($classSectionIds as $classSectionId) {
            //     $this->onlineExamQuestion->create([
            //         'question_id' => $onlineExamQuestion->id,
            //         'class_section_id' => $classSectionId,
            //         'class_subject_id' => $request->class_subject_id,
            //     ]);
            // }

            $sessionYear = $this->cache->getDefaultSessionYear();
            $this->sessionYearsTrackingsService->storeSessionYearsTracking('App\Models\OnlineExamQuestion', $onlineExamQuestion->id, Auth::user()->id, $sessionYear->id, Auth::user()->school_id, null);

            $onlineExamOptionData = array();
            foreach ($request->option_data as $key => $optionValue) {
                $onlineExamOptionData[$key] = array(
                    'question_id' => $onlineExamQuestion->id,
                    'option' => htmlspecialchars($optionValue['option']),
                    'is_answer' => 0, // Initialize is_answer to 0
                );
                foreach ($request->answer as $answerValue) {
                    if ($optionValue['number'] == $answerValue) {
                        $onlineExamOptionData[$key]['is_answer'] = 1; // Set is_answer to 1 if a match is found
                        break; // Break the loop as we've found a match
                    }
                }
            }

            foreach ($onlineExamOptionData as $option) {
                $onlineExamQuestionOption = $this->onlineExamQuestionOption->create($option);
                $sessionYear = $this->cache->getDefaultSessionYear();
                $this->sessionYearsTrackingsService->storeSessionYearsTracking('App\Models\OnlineExamQuestionOption', $onlineExamQuestionOption->id, Auth::user()->id, $sessionYear->id, Auth::user()->school_id, null);
            }

            DB::commit();

            ResponseService::successResponse('Data Stored Successfully', array(
                'exam_id' => $request->online_exam_id,
                'question_id' => $onlineExamQuestion->id,
                'question' => "<textarea id='qc" . $onlineExamQuestion->id . "'>" . htmlspecialchars_decode($onlineExamQuestion->question) . "</textarea><script>setTimeout(() => {equation_editor = CKEDITOR.inline('qc" . $onlineExamQuestion->id . "', { skin:'moono',extraPlugins: 'mathjax', mathJaxLib: 'https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.4/MathJax.js?config=TeX-AMS_HTML', readOnly:true, }); },1000);</script>"
            ));
        } catch (Throwable $e) {
            DB::rollback();
            ResponseService::logErrorResponse($e, "Online Exam Controller -> storeExamQuestionChoices method");
            ResponseService::errorResponse();
        }
    }

    public function getClassQuestions($onlineExamId)
    {
        ResponseService::noFeatureThenRedirect('Exam Management');
        ResponseService::noPermissionThenRedirect('online-exam-create');

        $offset = request('offset', 0);
        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'ASC');
        $search = request('search');
        $difficulty = request('difficulty');

        $onlineExamCommonData = $this->onlineExamCommon->builder()->where('online_exam_id', $onlineExamId)->get();
        $excludeQuestionId = $this->onlineExamQuestionChoice->builder()->where('online_exam_id', $onlineExamId)->pluck('question_id');

        // Initialize arrays to hold class and subject IDs
        $classIds = [];
        $classSubjectIds = [];
        // Loop through the online exam common data to extract IDs
        foreach ($onlineExamCommonData as $data) {
            // Get class_id from class_section_id
            $classSection = $this->classSection->builder()->where('id', $data->class_section_id)->first();
            if ($classSection) {
                $classIds[] = $classSection->class_id;
            }
            $classSubjectIds[] = $data->class_subject_id;
        }
        $classIds = array_unique($classIds);
        $classSubjectIds = array_unique($classSubjectIds);

        $sql = $this->onlineExamQuestion->builder()
            ->with('options')
            ->whereIn('class_id', $classIds)
            ->whereIn('class_subject_id', $classSubjectIds)
            ->whereNotIn('id', $excludeQuestionId)
            ->where(function ($query) use ($search) {
                $query->when($search, function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('id', 'LIKE', "%$search%")
                            ->orWhere('question', 'LIKE', "%$search%")
                            ->orWhere('created_at', 'LIKE', "%" . date('Y-m-d H:i:s', strtotime($search)) . "%")
                            ->orWhere('updated_at', 'LIKE', "%" . date('Y-m-d H:i:s', strtotime($search)) . "%")
                            ->orWhereHas('options', function ($p) use ($search) {
                                $p->where('option', 'LIKE', "%$search%");
                            });
                    });
                });
            })
            ->when($difficulty, function ($query) use ($difficulty) {
                $query->where('difficulty', $difficulty);
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
        $tempRow = array();
        $no = 1;
        foreach ($res as $row) {

            $tempRow['question_id'] = $row->id;
            $tempRow['no'] = $no++;
            $tempRow['class_section_id'] = $row->class_section_id;
            $tempRow['class_section_name'] = $row->class_section_with_medium;
            $tempRow['class_subject_id'] = $row->class_subject_id;
            $tempRow['subject_name'] = $row->subject_with_name;
            $tempRow['question'] = "<div class='equation-editor-inline' contenteditable=false name='qc" . $row->id . "'>" . htmlspecialchars_decode($row->question) . "</div>";
            $tempRow['question_row'] = htmlspecialchars_decode($row->question);

            $tempRow['options'] = array();
            $tempRow['answers'] = array();

            foreach ($row->options as $options) {
                $option_data = array(
                    'id' => $options->id,
                    'option' => "<div class='equation-editor-inline' contenteditable=false>" . htmlspecialchars_decode($options->option) . "</div>",
                    'option_row' => htmlspecialchars_decode($options->option)
                );
                $tempRow['options'][] = $option_data;
                if ($options->is_answer) {
                    $answer_data = array(
                        'id' => $options->id,
                        'answer' => "<div class='equation-editor-inline' contenteditable=false>" . htmlspecialchars_decode($options->option) . "</div>",
                    );
                    $tempRow['answers'][] = $answer_data;
                }
            }

            $tempRow['image'] = $row->image_url;
            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    public function storeQuestionsChoices(Request $request)
    {
        ResponseService::noFeatureThenRedirect('Exam Management');
        ResponseService::noPermissionThenRedirect('online-exam-create');
        $request->validate([
            'exam_id' => 'required',
            'assign_questions.*.question_id' => 'required',
            'assign_questions.*.marks' => 'required|numeric'
        ], [
            'assign_questions.*.marks.required' => trans('marks_are_required')
        ]);

        try {
            DB::beginTransaction();

            $onlineExamQuestionChoiceData = array();
            foreach ($request->assign_questions as $question) {
                $onlineExamQuestionChoiceData[] = array(
                    'id' => $question['edit_id'] ?? null,
                    'online_exam_id' => $request->exam_id,
                    'question_id' => $question['question_id'],
                    'marks' => $question['marks']
                );
            }
            $this->onlineExamQuestionChoice->upsert($onlineExamQuestionChoiceData, ["id"], ['online_exam_id', 'question_id', 'marks']);
            // $sessionYear = $this->cache->getDefaultSessionYear();
            // $this->sessionYearsTrackingsService->storeSessionYearsTracking('App\Models\OnlineExamQuestionChoice', $onlineExamQuestionChoiceData['question_id'], Auth::user()->id, $sessionYear->id, Auth::user()->school_id, null);

            $examData = $this->onlineExam->builder()->where('id', $request->exam_id)->first();
            $subjectName = $examData->class_subject->subject;
            $students = $this->student->builder()->where('application_status', 1)->where('class_section_id', $examData->class_section->id)->whereHas('user', function ($query) {
                $query->where('status', 1)->withTrashed();
            })->get(['user_id', 'guardian_id']);
            $guardians = $students->pluck('guardian_id');
            $users = $students->pluck('user_id');
            $userIds = array_merge($users->toArray(), $guardians->toArray());
            $customData = ['exam_type' => 'online', 'exam_id' => $examData->id, 'exam_title' => $examData->title];

            send_notification($userIds, 'Online Exam Alert !!!', 'New online exam added for ' . $subjectName->name . '(' . $subjectName->type . ')', 'exam', $customData);

            DB::commit();
            ResponseService::successResponse('Data Stored Successfully');
        } catch (Throwable $e) {
            DB::rollback();
            ResponseService::logErrorResponse($e, "Online Exam Controller -> storeQuestionsChoices method");
            ResponseService::errorResponse();
        }
    }

    public function removeQuestionsChoices($id)
    {
        ResponseService::noFeatureThenRedirect('Exam Management');
        ResponseService::noPermissionThenRedirect('online-exam-create');
        try {
            $student_submitted_answers = $this->onlineExamStudentAnswer->builder()->where('question_id', $id)->count();
           // dd($student_submitted_answers);
            if ($student_submitted_answers) {
                ResponseService::errorResponse("cannot delete because data is associated with other data");
            } else {
                DB::beginTransaction();
                $this->onlineExamQuestionChoice->deleteById($id);
                $sessionYear = $this->cache->getDefaultSessionYear();
                $this->sessionYearsTrackingsService->storeSessionYearsTracking('App\Models\OnlineExamQuestionChoice', $id, Auth::user()->id, $sessionYear->id, Auth::user()->school_id, null);
                DB::commit();
                ResponseService::successResponse('Data Deleted Successfully');
            }
        } catch (Throwable $e) {
            DB::rollback();
            ResponseService::logErrorResponse($e, "Online Exam Controller -> removeQuestionsChoices method");
            ResponseService::errorResponse();
        }
    }

    public function storeRandomQuestionsChoices(Request $request)
    {

        ResponseService::noFeatureThenRedirect('Exam Management');
        ResponseService::noPermissionThenRedirect('online-exam-create');
        $request->validate([
            'exam_id' => 'required',
            'class_section_id' => 'required',
            'class_subject_id' => 'required',
            'total_questions' => 'required|numeric|min:1',
            'total_marks' => 'required|numeric|min:1',
            'difficulty' => 'required|in:all,easy,medium,hard',
        ]);

        $onlineExamId = $request->exam_id;
        $numberOfQuestions = $request->total_questions;
        $difficulty = $request->difficulty;

        if (is_float($request->total_marks / $numberOfQuestions)) {
            ResponseService::errorResponse("Please enter a number that, when divided by the specified divisor, results in a whole number (integer) rather than a decimal (float).");
        }

        $string = trim($request->class_section_id, '[]'); // Remove square brackets
        $classSectionIds = explode(',', $string);
        foreach ($classSectionIds as $key => $value) {
            $classId = $this->classSection->builder()->where('id', $value)->first()->class_id;
        }

        $string = trim($request->class_subject_id, '[]'); // Remove square brackets
        $classSubjectIds = explode(',', $string);



        $questionIds = $this->onlineExamQuestion->builder()
            ->whereIn('class_id', [$classId])
            ->whereIn('class_subject_id', $classSubjectIds)
            ->when($difficulty !== 'all', function ($q) use ($difficulty) {
                $q->where('difficulty', $difficulty);
            })
            ->limit($numberOfQuestions)
            ->inRandomOrder()
            ->get()->pluck('id')->toArray();

        if (count($questionIds) < $numberOfQuestions) {
            ResponseService::errorResponse("Not enough questions available for the selected criteria.");
        }

        try {
            DB::beginTransaction();
            $data = [];
            foreach ($questionIds as $questionId) {
                $data[] = array(
                    'online_exam_id' => $onlineExamId,
                    'question_id' => $questionId,
                    'marks' => $request->total_marks / $numberOfQuestions,
                    'school_id' => Auth::user()->school_id,
                );
            }

            $this->onlineExamQuestionChoice->upsert($data, ["id"], ['online_exam_id', 'question_id', 'marks']);
            DB::commit();
            ResponseService::successResponse('Data Stored Successfully');
        } catch (Throwable $e) {
            DB::rollback();
            ResponseService::logErrorResponse($e, "Online Exam Controller -> storeRandomQuestionsChoices method");
            ResponseService::errorResponse();
        }
    }

    public function onlineExamResultIndex($id)
    {
        ResponseService::noFeatureThenRedirect('Exam Management');
        ResponseService::noPermissionThenRedirect('online-exam-result-list');
        $onlineExamData = $this->onlineExam->findById($id, ['*'], ['class_subject', 'class_section']);
        $onlineExamCommons = OnlineExamCommon::where('online_exam_id', $id)->with('class_section')->get()->pluck('class_section_with_medium', 'class_section_id')->toArray();
        return response(view('online_exam.online_exam_result', compact('onlineExamData', 'onlineExamCommons')));
    }

    // To Be Optimised With API

    public function showOnlineExamResult($id)
    {
        ResponseService::noPermissionThenRedirect('online-exam-list');
        $offset = request('offset', 0);
        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'ASC');


        $sql = $this->studentOnlineExamStatus->builder()->with('student_data', 'online_exam.question_choice')->where(['online_exam_id' => $id, 'status' => 2]);
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
        $tempRow = array();
        $no = 1;
        foreach ($res as $student_attempt) {
            //get the total marks and obtained marks
            //            $total_obtained_marks = 0;
            //            $total_marks = 0;

            $exam_submitted_question_ids = $this->onlineExamStudentAnswer->builder()->where(['student_id' => $student_attempt->student_id, 'online_exam_id' => $student_attempt->online_exam_id])->pluck('question_id');

            $question_ids = $this->onlineExamQuestionChoice->builder()->whereIn('id', $exam_submitted_question_ids)->pluck('question_id');


            $exam_attempted_answers = $this->onlineExamStudentAnswer->builder()->where(['student_id' => $student_attempt->student_id, 'online_exam_id' => $student_attempt->online_exam_id])->pluck('option_id');

            //removes the question id of the question if one of the answer of particular question is wrong
            foreach ($question_ids as $question_id) {
                $check_questions_answers_exists = $this->onlineExamQuestionOption->builder()->where(['question_id' => $question_id, 'is_answer' => 1])->whereNotIn('id', $exam_attempted_answers)->count();
                if ($check_questions_answers_exists) {
                    unset($question_ids[array_search($question_id, $question_ids->toArray())]);
                }
            }

            $exam_correct_answers_question_id = $this->onlineExamQuestionOption->builder()->where(['is_answer' => 1])->whereIn('id', $exam_attempted_answers)->whereIn('question_id', $question_ids)->pluck('question_id');

            // get the data of only attempted data
            $total_obtained_marks = $this->onlineExamQuestionChoice->builder()->select(DB::raw("sum(marks)"))->where('online_exam_id', $student_attempt->online_exam_id)->whereIn('question_id', $exam_correct_answers_question_id)->first();
            $total_obtained_marks = $total_obtained_marks['sum(marks)'];
            $total_marks = $this->onlineExamQuestionChoice->builder()->select(DB::raw("sum(marks)"))->where('online_exam_id', $student_attempt->online_exam_id)->first();
            $total_marks = $total_marks['sum(marks)'];

            $tempRow['student_id'] = $student_attempt->student_id;
            $tempRow['no'] = $no++;
            $tempRow['student_name'] = $student_attempt->student_data->full_name;
            if ($total_obtained_marks) {
                $tempRow['marks'] = $total_obtained_marks . ' / ' . $total_marks;
            } else {
                $total_obtained_marks = 0;
                $tempRow['marks'] = $total_obtained_marks . ' / ' . $total_marks;
            }
            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }
}
