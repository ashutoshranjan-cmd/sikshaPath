<?php

namespace App\Http\Controllers;

use App\Exports\OnlineExamQuestionsExport;
use App\Imports\OnlineExamQuestionsImport;
use App\Models\OnlineExamCommon;
use App\Repositories\ClassSection\ClassSectionInterface;
use App\Repositories\ClassSchool\ClassSchoolInterface;
use App\Repositories\ClassSubject\ClassSubjectInterface;
use App\Repositories\OnlineExamQuestion\OnlineExamQuestionInterface;
use App\Repositories\OnlineExamQuestionChoice\OnlineExamQuestionChoiceInterface;
use App\Repositories\OnlineExamQuestionOption\OnlineExamQuestionOptionInterface;
use App\Repositories\SubjectTeacher\SubjectTeacherInterface;
use App\Services\BootstrapTableService;
use App\Services\CachingService;
use App\Services\ResponseService;
use App\Services\SessionYearsTrackingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Str;
use Throwable;
use TypeError;

class OnlineExamQuestionController extends Controller
{

    private SubjectTeacherInterface $subjectTeacher;
    private ClassSectionInterface $classSection;
    private OnlineExamQuestionInterface $onlineExamQuestion;
    private OnlineExamQuestionChoiceInterface $onlineExamQuestionChoice;
    private OnlineExamQuestionOptionInterface $onlineExamQuestionOption;
    private CachingService $cache;
    private ClassSubjectInterface $classSubjects;
    private ClassSchoolInterface $classSchool;
    private SessionYearsTrackingsService $sessionYearsTrackingsService;

    public function __construct(SubjectTeacherInterface $subjectTeacher, ClassSectionInterface $classSection, OnlineExamQuestionInterface $onlineExamQuestion, OnlineExamQuestionChoiceInterface $onlineExamQuestionChoice, OnlineExamQuestionOptionInterface $onlineExamQuestionOption, CachingService $cache, ClassSubjectInterface $classSubjects, ClassSchoolInterface $classSchool, SessionYearsTrackingsService $sessionYearsTrackingsService)
    {
        $this->subjectTeacher = $subjectTeacher;
        $this->classSection = $classSection;
        $this->onlineExamQuestion = $onlineExamQuestion;
        $this->onlineExamQuestionChoice = $onlineExamQuestionChoice;
        $this->onlineExamQuestionOption = $onlineExamQuestionOption;
        $this->cache = $cache;
        $this->classSubjects = $classSubjects;
        $this->classSchool = $classSchool;
        $this->sessionYearsTrackingsService = $sessionYearsTrackingsService;
    }

    public function index()
    {
        ResponseService::noFeatureThenRedirect('Exam Management');
        ResponseService::noPermissionThenRedirect('online-exam-questions-list');
        // $subjectTeachers = $this->subjectTeacher->builder()->with('subject:id,name,type')->get();

        $subjectTeachers = array();
        $classSubjects = array();
        if (Auth::user()->hasRole('School Admin')) {
            $classSubjects = $this->classSubjects->builder()->with('subject')->get();
        } else {
            $subjectTeachers = $this->subjectTeacher->builder()->with('subject')->get();
        }
        $classes = $this->classSchool->builder()->with('medium')->get();

        return response(view('online_exam.class_questions', compact('classes', 'subjectTeachers', 'classSubjects')));
    }

    public function store(Request $request)
    {
        ResponseService::noFeatureThenRedirect('Exam Management');
        ResponseService::noPermissionThenRedirect('online-exam-questions-create');
        try {
            DB::beginTransaction();
            if (empty($request->class_id)) {
                throw new \Exception('Class ID is required');
            }

            $onlineExamQuestionData = [];

            // Get the class to set initial data
            $class = $this->classSchool->builder()
                ->where('id', $request->class_id)
                ->firstOrFail();

            $classSubjects = $this->classSubjects->builder()
                ->where('class_id', $class->id)
                ->where('subject_id', $request->subject_id)
                ->firstOrFail();
            $request->merge(['question' => htmlspecialchars($request->question, ENT_QUOTES | ENT_HTML5)]);
            $onlineExamQuestionData = array_merge($request->all(), [
                'class_id' => $request->class_id,
                'class_subject_id' => $classSubjects->id,
                'last_edited_by' => Auth::user()->id
            ]);

            $onlineExamQuestion = $this->onlineExamQuestion->create($onlineExamQuestionData);

            // Create options
            $onlineExamOptionData = array();
            foreach ($request->option_data as $key => $optionValue) {
                $onlineExamOptionData[$key] = array(
                    'question_id' => $onlineExamQuestion->id,
                    'option' => htmlspecialchars($optionValue['option'], ENT_QUOTES | ENT_HTML5),
                    'is_answer' => 0
                );

                if (isset($request->answer)) {
                    foreach ($request->answer as $answerValue) {
                        if ($optionValue['number'] == $answerValue) {
                            $onlineExamOptionData[$key]['is_answer'] = 1;
                        }
                    }
                }
            }
            $this->onlineExamQuestionOption->createBulk($onlineExamOptionData);

            // Store session tracking
            $sessionYear = $this->cache->getDefaultSessionYear();
            $semester = $this->cache->getDefaultSemesterData();

            $this->sessionYearsTrackingsService->storeSessionYearsTracking(
                'App\Models\OnlineExamQuestion',
                $onlineExamQuestion->id,
                Auth::user()->id,
                $sessionYear->id,
                Auth::user()->school_id,
                $semester ? $semester->id : null
            );

            DB::commit();
            ResponseService::successResponse('Data Stored Successfully');
        } catch (Throwable $e) {
            DB::rollback();
            ResponseService::logErrorResponse($e, "Online Exam Question Controller -> Store method");
            ResponseService::errorResponse();
        }
    }


    public function show()
    {
        ResponseService::noFeatureThenRedirect('Exam Management');
        ResponseService::noPermissionThenRedirect('online-exam-questions-list');
        $offset = request('offset', 0);
        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'ASC');
        $search = request('search');
        $subject_id = request('subject_id');



        $sql = $this->onlineExamQuestion->builder()->with('options', 'class.medium', 'class_subject.subject')
            ->where(function ($query) use ($search, $subject_id) {
                $query->when($search, function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('id', 'LIKE', "%$search%")
                            ->orWhere('question', 'LIKE', "%$search%")
                            ->orWhere('difficulty', 'LIKE', "%$search%")
                            ->orWhere('created_at', 'LIKE', "%" . date('Y-m-d H:i:s', strtotime($search)) . "%")
                            ->orWhere('updated_at', 'LIKE', "%" . date('Y-m-d H:i:s', strtotime($search)) . "%")
                            ->orWhereHas('options', function ($p) use ($search) {
                                $p->where('option', 'LIKE', "%$search%");
                            })->orWhereHas('class_subject.subject', function ($sub) use ($search) {
                                $sub->where('name', 'LIKE', "%{$search}%");
                            })->orWhereHas('class', function ($c) use ($search) {
                                $c->whereRaw("CONCAT(classes.name, ' - ', (SELECT mediums.name FROM mediums WHERE mediums.id = classes.medium_id LIMIT 1)) LIKE ?", ["%{$search}%"]);
                            });
                    });
                })
                    ->when(request('class_section_id'), function ($query) {
                        $query->where('class_id', request('class_section_id'));
                    })
                    ->when(request('class_subject_id'), function ($query) {
                        $query->whereHas('class_subject', function ($q) {
                            $q->where('subject_id', request('class_subject_id'));
                        });
                    });
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
            // data for options which not answers
            $operate = BootstrapTableService::button('fa fa-edit', route('online-exam-question.edit', $row->id), ['btn-gradient-primary'], ['title' => 'Edit']); // Timetable Button
            $operate .= BootstrapTableService::trashButton(route('online-exam-question.destroy', $row->id));

            $tempRow['online_exam_question_id'] = $row->id;
            $tempRow['no'] = $no++;
            $tempRow['class_name'] = $row->class_with_medium ?? '';
            $tempRow['subject_name'] = $row->subject_with_name ?? '';
            $tempRow['question'] = htmlspecialchars_decode($row->question);
            $tempRow['question_row'] = htmlspecialchars_decode($row->question);
            //options data
            $tempRow['options'] = array();
            $tempRow['answers'] = array();
            foreach ($row->options as $options) {
                $tempRow['options'][] = array(
                    'id' => $options->id,
                    'option' => "<div class='equation-editor-inline' contenteditable=false>" . $options->option . "</div>",
                    'option_row' => $options->option
                );
                if ($options->is_answer) {
                    $tempRow['answers'][] = array(
                        'id' => $options->id,
                        'answer' => "<div class='equation-editor-inline' contenteditable=false>" . $options->option . "</div>",
                        'option_row' => $options->option
                    );
                }
            }
            $tempRow['image'] = $row->image_url;
            $tempRow['note'] = htmlspecialchars_decode($row->note);
            $tempRow['difficulty'] = $row->difficulty;
            $tempRow['created_at'] = $row->created_at;
            $tempRow['updated_at'] = $row->updated_at;
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    public function edit($id)
    {
        ResponseService::noFeatureThenRedirect('Exam Management');
        ResponseService::noPermissionThenRedirect('online-exam-questions-edit');

        $onlineExamQuestion = $this->onlineExamQuestion->builder()->where('id', $id)
            ->with('options', 'class.medium', 'class_subject.subject')
            ->first();

        return response(view('online_exam.edit_class_questions', compact('onlineExamQuestion')));
    }

    public function update(Request $request, $id)
    {

        ResponseService::noFeatureThenRedirect('Exam Management');
        ResponseService::noPermissionThenSendJson('online-exam-questions-edit');
        try {
            DB::beginTransaction();
            $onlineExamQuestionData = array(
                'question' => htmlspecialchars($request->question, ENT_QUOTES | ENT_HTML5),
                'note' => $request->note,
                'difficulty' => $request->difficulty,
                'last_edited_by' => Auth::user()->id,
            );
            if (!empty($request->image)) {
                $onlineExamQuestionData['image_url'] = $request->image;
            }
            $onlineExamQuestion = $this->onlineExamQuestion->update($id, $onlineExamQuestionData);

            $onlineExamOptionData = array();
            foreach ($request->option_data as $key => $optionValue) {
                $onlineExamOptionData[$key] = array(
                    'id' => $optionValue['id'],
                    'question_id' => $onlineExamQuestion->id,
                    'option' => htmlspecialchars($optionValue['option'], ENT_QUOTES | ENT_HTML5),
                    'is_answer' => 0, // Initialize is_answer to 0
                );
                foreach ($request->answer as $answerValue) {
                    if ($optionValue['number'] == $answerValue) {
                        $onlineExamOptionData[$key]['is_answer'] = 1; // Set is_answer to 1 if a match is found
                        break; // Break the loop as we've found a match
                    }
                }
            }
            $this->onlineExamQuestionOption->upsert($onlineExamOptionData, ["id"], ["question_id", "option", "is_answer"]);
            DB::commit();
            ResponseService::successResponse('Data Updated Successfully');
        } catch (Throwable $e) {
            DB::rollback();
            ResponseService::logErrorResponse($e, "Online Exam Question Controller -> Update method");
            ResponseService::errorResponse();
        }
    }


    public function destroy($id)
    {
        ResponseService::noFeatureThenRedirect('Exam Management');
        ResponseService::noPermissionThenSendJson('online-exam-questions-delete');
        try {
            // Check if the question is assigned to any online exam
            $isAssigned = $this->onlineExamQuestionChoice->builder()
                ->where('question_id', $id)
                ->exists();

            if ($isAssigned) {
                ResponseService::errorResponse(trans('cannot_delete_because_data_is_associated_with_other_data'));
            }

            $this->onlineExamQuestion->deleteById($id);
            $sessionYear = $this->cache->getDefaultSessionYear();
            $semester = $this->cache->getDefaultSemesterData();
            if ($semester) {
                $this->sessionYearsTrackingsService->deleteSessionYearsTracking('App\Models\OnlineExamQuestion', $id, Auth::user()->id, $sessionYear->id, Auth::user()->school_id, $semester->id);
            } else {
                $this->sessionYearsTrackingsService->deleteSessionYearsTracking('App\Models\OnlineExamQuestion', $id, Auth::user()->id, $sessionYear->id, Auth::user()->school_id, null);
            }
            ResponseService::successResponse('Data Deleted Successfully');
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e, "Online Exam Question Controller -> Destroy method", 'cannot_delete_because_data_is_associated_with_other_data');
            ResponseService::errorResponse();
        }
    }

    public function removeOptions($id)
    {
        ResponseService::noFeatureThenRedirect('Exam Management');
        ResponseService::noPermissionThenRedirect('online-exam-questions-delete');
        try {
            $this->onlineExamQuestionOption->deleteById($id);
            ResponseService::successResponse('Data Deleted Successfully');
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e, "Online Exam Question Controller -> Remove Options method", trans('cannot_delete_because_data_is_associated_with_other_data'));
            ResponseService::errorResponse();
        }
    }

    public function createBulkQuestions()
    {
        ResponseService::noFeatureThenRedirect('Exam Management');
        ResponseService::noPermissionThenRedirect('online-exam-questions-create');
        $subjectTeachers = array();
        $classSubjects = array();
        if (Auth::user()->hasRole('School Admin')) {
            $classSubjects = $this->classSubjects->builder()->with('subject')->get();
        } else {
            $subjectTeachers = $this->subjectTeacher->builder()->with('subject')->get();
        }
        $classes = $this->classSchool->builder()->with('medium')->get();
        return view('online_exam.add_bulk_questions', compact('classes', 'subjectTeachers', 'classSubjects'));
    }

    public function getSubjectsByClass(Request $request)
    {
        $classId = $request->class_id;

        if (!$classId) {
            return response()->json(['error' => 'Class ID is required'], 400);
        }

        if (Auth::user()->hasRole('Teacher')) {
            // Teachers can only see subjects they are assigned to for this class
            $subjects = $this->subjectTeacher->builder()
                ->whereHas('class_section', function ($query) use ($classId) {
                    $query->where('class_id', $classId);
                })
                ->where('teacher_id', Auth::user()->id)
                ->with(['subject', 'class_subject'])
                ->get()
                ->map(function ($subjectTeacher) {
                    return [
                        'id' => $subjectTeacher->class_subject_id,
                        'subject_id' => $subjectTeacher->subject_id,
                        'subject_name' => $subjectTeacher->subject->name,
                        'subject_type' => $subjectTeacher->class_subject->type ?? 'Compulsory',
                        'subject_with_name' => $subjectTeacher->subject_with_name
                    ];
                })
                ->unique('subject_id')
                ->values();
        } else {
            // School Admin can see all subjects for the class
            $subjects = $this->classSubjects->builder()
                ->where('class_id', $classId)
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

    public function downloadSampleFile()
    {
        try {
            // return Excel::download(new OnlineExamQuestionsExport(), 'Online_Exam_Questions_import.xlsx');
            return response()->download(public_path('assets/files/Online_Exam_Questions_import.xlsx'));
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e, 'Online Exam Question Controller ---> Download Sample File');
            ResponseService::errorResponse();
        }
    }

    public function storeBulkData(Request $request)
    {
        // dd($request->all());
        ResponseService::noFeatureThenRedirect('Exam Management');
        ResponseService::noPermissionThenRedirect('online-exam-questions-create');
        $validator = Validator::make($request->all(), [
            'subject_id' => 'required|numeric',
            'class_id' => 'required',
            'file' => 'required|mimes:csv,txt'
        ]);
        if ($validator->fails()) {
            ResponseService::errorResponse($validator->errors()->first());
        }
        try {
            Excel::import(new OnlineExamQuestionsImport($request->class_id, $request->subject_id), $request->file);
            ResponseService::successResponse('Data Stored Successfully');
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e, "Online Exam Question Controller -> Store Bulk method");
            ResponseService::errorResponse();
        }
    }
}
