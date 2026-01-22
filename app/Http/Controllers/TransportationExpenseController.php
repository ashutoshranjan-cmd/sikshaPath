<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Expense\ExpenseInterface;
use App\Repositories\ExpenseCategory\ExpenseCategoryInterface;
use App\Repositories\SessionYear\SessionYearInterface;
use App\Services\SessionYearsTrackingsService;
use App\Services\BootstrapTableService;
use App\Services\CachingService;
use App\Services\ResponseService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Vehicle;
use Throwable;

class TransportationExpenseController extends Controller
{
    private ExpenseInterface $expense;
    private ExpenseCategoryInterface $expenseCategory;
    private SessionYearInterface $sessionYear;
    private CachingService $cache;
    private SessionYearsTrackingsService $sessionYearsTrackingsService;

    public function __construct(ExpenseInterface $expense, ExpenseCategoryInterface $expenseCategory, SessionYearInterface $sessionYear, CachingService $cache, SessionYearsTrackingsService $sessionYearsTrackingsService)
    {
        $this->expense = $expense;
        $this->expenseCategory = $expenseCategory;
        $this->sessionYear = $sessionYear;
        $this->cache = $cache;
        $this->sessionYearsTrackingsService = $sessionYearsTrackingsService;
    }

    public function index()
    {
        ResponseService::noAnyPermissionThenRedirect(['transportationexpense-create', 'transportationexpense-list']);

        $expenseCategory = $this->expenseCategory->builder()->pluck('name', 'id')->toArray();
        $sessionYear = $this->sessionYear->builder()->pluck('name', 'id');
        $current_session_year = app(CachingService::class)->getDefaultSessionYear();
        $vehicles = Vehicle::where('status', 1)->get(['name', 'id', 'vehicle_number']);
        $schoolSetting = $this->cache->getSchoolSettings();

        return view('transportation-expense.index', compact('expenseCategory', 'sessionYear', 'current_session_year', 'vehicles', 'schoolSetting'));
    }

    public function store(Request $request)
    {
        ResponseService::noPermissionThenSendJson('transportationexpense-create');
        $validator = Validator::make($request->all(), [
            'vehicle_id' => 'required|integer|exists:vehicles,id',
            'title' => 'required',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'image_pdf' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:4096',
            'session_year_id' => 'nullable|integer|exists:session_years,id',
        ]);

        if ($validator->fails()) {
            ResponseService::validationError($validator->errors()->first());
        }
        try {
            DB::beginTransaction();
            $data = [
                'vehicle_id' => $request->vehicle_id,
                'category_id' => $request->category_id,
                'title' => $request->title,
                'ref_no' => $request->ref_no,
                'amount' => $request->amount,
                'date' => date('Y-m-d', strtotime($request->date)),
                'description' => $request->description,
                'session_year_id' => $request->session_year_id,
                'file' => $request->file('image_pdf'),
                'created_by' => Auth::user()->id
            ];
            $expense = $this->expense->create($data);

            $sessionYear = $this->cache->getDefaultSessionYear();
            $this->sessionYearsTrackingsService->storeSessionYearsTracking('App\Models\Expense', $expense->id, Auth::user()->id, $sessionYear->id, Auth::user()->school_id, null);

            DB::commit();
            ResponseService::successResponse('Data Stored Successfully');
        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e, "TransportationExpenseController Controller -> Store Method");
            ResponseService::errorResponse();
        }
    }

    public function show($id)
    {
        ResponseService::noPermissionThenRedirect('transportationexpense-list');
        $offset = request('offset', 0);
        $limit = request('limit', 10);
        $sort = request('sort', 'date');
        $order = request('order', 'DESC');
        $search = request('search');
        $category_id = request('category_id');
        $vehicle_id = request('vehicle_id');
        $session_year_id = request('session_year_id');

        $sql = $this->expense->builder()->with('category', 'vehicle', 'created_by')->whereNotNull("vehicle_id")->where(function ($query) use ($search) {
            $query->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'LIKE', "%$search%")->orWhere('ref_no', 'LIKE', "%$search%")->orWhere('amount', 'LIKE', "%$search%")->orWhere('date', 'LIKE', "%$search%")->orWhere('description', 'LIKE', "%$search%")->orWhereHas('category', function ($q) use ($search) {
                        $q->Where('name', 'LIKE', "%$search%");
                    });
                });
            });
        });

        if ($category_id) {
            if ($category_id != 'salary') {
                $sql->where('category_id', $category_id)->whereNull('staff_id');
            } else {
                $sql->whereNotNull('staff_id');

            }
        }

        if ($vehicle_id) {
            $sql->where('vehicle_id', $vehicle_id);
        }

        if ($session_year_id) {
            $sql->where('session_year_id', $session_year_id);
        }

        $total = $sql->get()->count();
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

        foreach ($res as $row) {
            $operate = '';
            if (!$row->month) {
                $operate .= BootstrapTableService::editButton(route('transportation-expense.update', $row->id));
                $operate .= BootstrapTableService::deleteButton(route('expense.destroy', $row->id));
            }

            $tempRow = $row->toArray();
            $tempRow['no'] = $no++;
            $tempRow['amount'] = $row->amount;
            $tempRow['vehicle'] = $row->vehicle->name . " (" . $row->vehicle->vehicle_number . ")";
            $fileUrl = $row->file ?? null;
            $fileExtension = '';
            if (!empty($fileUrl)) {
                $fileExtension = strtolower(pathinfo($fileUrl, PATHINFO_EXTENSION));
            }
            $previewHtml = '';
            if ($fileExtension) {
                if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])) {
                    $previewHtml = '
                    <a href="' . $fileUrl . '" target="_blank" class="btn btn-sm btn-outline-info w-100 mt-2">
                        View Image
                    </a>';
                } elseif ($fileExtension === 'pdf') {
                    $previewHtml = '
                    <a href="' . $fileUrl . '" target="_blank" class="btn btn-sm btn-outline-info w-100 mt-2">
                        View PDF
                    </a>';
                } else {
                    $previewHtml = '<span class="text-danger">Unsupported file type</span>';
                }
            }
            $tempRow['file'] = $previewHtml;
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    public function update(Request $request, $id)
    {
        ResponseService::noPermissionThenSendJson('transportationexpense-edit');
        $validator = Validator::make($request->all(), [
            'vehicle_id' => 'required|integer|exists:vehicles,id',
            'title' => 'required',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'image_pdf' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:4096'
        ]);

        if ($validator->fails()) {
            ResponseService::validationError($validator->errors()->first());
        }
        try {
            DB::beginTransaction();
            $data = [
                'vehicle_id' => $request->vehicle_id,
                'category_id' => $request->category_id,
                'title' => $request->title,
                'ref_no' => $request->ref_no,
                'amount' => $request->amount,
                'date' => date('Y-m-d', strtotime($request->date)),
                'description' => $request->description,
                'session_year_id' => $request->session_year_id
            ];
            if ($request->hasFile('image_pdf')) {
                $data['file'] = $request->file('image_pdf');
            }
            $this->expense->update($id, $data);
            DB::commit();
            ResponseService::successResponse('Data Updated Successfully');
        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e, "TransportationExpenseController Controller -> Update Method");
            ResponseService::errorResponse();
        }
    }
}
