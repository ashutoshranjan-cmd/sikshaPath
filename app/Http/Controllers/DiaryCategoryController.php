<?php

namespace App\Http\Controllers;

use App\Repositories\DiaryCategory\DiaryCategoryInterface;
use App\Repositories\Diary\DiaryInterface;
use App\Services\BootstrapTableService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class DiaryCategoryController extends Controller
{
    private DiaryCategoryInterface $diaryCategory;
    private DiaryInterface $diary;

    public function __construct(DiaryCategoryInterface $diaryCategory, DiaryInterface $diary)
    {
        $this->diaryCategory = $diaryCategory;
        $this->diary = $diary;
    }

    public function index()
    {
        return view('diary-category.index');
    }
    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        ResponseService::noPermissionThenSendJson('student-diary-create');
        $request->validate([
            'name' => 'required',
            'type' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $data = [
                ...$request->all()
            ];
            $this->diaryCategory->create($data);
            DB::commit();
            ResponseService::successResponse('Data Stored Successfully');
        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e, 'Diary Category Controller -> Store method');
            ResponseService::errorResponse();
        }
    }

    public function show($id)
    {
        $offset = request('offset', 0);
        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'DESC');
        $search = request('search');
        $showDeleted = request('show_deleted');
        $sql = $this->diaryCategory->builder()
            ->where(function ($query) use ($search) {
                $query->when($search, function ($query) use ($search) {
                    $query->where('id', 'LIKE', "%$search%")
                        ->orwhere('name', 'LIKE', "%$search%")
                        ->orwhere('type', 'LIKE', "%$search%");
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
            
            if (empty($showDeleted)) {
                $operate .= BootstrapTableService::editButton(route('diary-categories.update', $row->id), true);
                $operate .= BootstrapTableService::deleteButton(route('diary-categories.destroy', $row->id));
            } else {
                $operate .= BootstrapTableService::restoreButton(route('diary-categories.restore', $row->id));
                $operate .= BootstrapTableService::trashButton(route('diary-categories.trash', $row->id));
            }

            $tempRow = $row->toArray();
            $tempRow['no'] = $no++;
            // $tempRow['operate'] = BootstrapTableService::menuItem($operate);
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    public function edit($id)
    {
        $diaryCategory = $this->diaryCategory->findById($id);
        return response($diaryCategory);
    }

    public function update(Request $request, $id)
    {
        ResponseService::noPermissionThenSendJson('student-diary-edit');
        $request->validate([
            'name' => 'required',
            'type' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $data = [
                ...$request->all()
            ];
            $this->diaryCategory->update($id, $data);
            DB::commit();
            ResponseService::successResponse('Data Updated Successfully');
        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e, 'Diary Category Controller -> Store method');
            ResponseService::errorResponse();
        }
    }

    public function trash($id)
    {
        ResponseService::noPermissionThenSendJson('student-diary-delete');
        try {
            DB::beginTransaction();
            $existing_data = $this->diary->builder()->where('diary_category_id', $id)->get();
            if (count($existing_data) > 0) {
                return ResponseService::errorResponse('This Category is already used in Diary. You can not delete this.');
            }
            $this->diaryCategory->findTrashedById($id)->forceDelete();
            DB::commit();
            ResponseService::successResponse('Data Deleted Successfully');
        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e, 'Diary Category Controller ->trash Method');
            ResponseService::errorResponse();
        }
    }

    public function restore($id)
    {
        ResponseService::noPermissionThenSendJson('student-diary-delete');
        try {
            DB::beginTransaction();
            $this->diaryCategory->restoreById($id);
            DB::commit();
            ResponseService::successResponse('Data Restored Successfully');
        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e, 'Diary Category Controller -> Restore method');
            ResponseService::errorResponse();
        }
    }

    public function destroy($id)
    {
        ResponseService::noPermissionThenSendJson('student-diary-delete');
        try {
            DB::beginTransaction();
            $existing_data = $this->diary->builder()->where('diary_category_id', $id)->get();
            if (count($existing_data) > 0) {
                return ResponseService::errorResponse('This Category is already used in Diary. You can not delete this.');
            }
            $this->diaryCategory->deleteById($id);
            // $this->diaryCategory->findOnlyTrashedById($id);
            DB::commit();
            ResponseService::successResponse('Data Deleted Successfully');
        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e, 'Diary Category Controller -> Destroy method');
            ResponseService::errorResponse();
        }
    }
}
