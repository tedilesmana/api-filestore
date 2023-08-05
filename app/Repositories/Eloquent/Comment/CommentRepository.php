<?php

namespace App\Repositories\Eloquent\Comment;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Comment\CommentResource;
use App\Models\Comment;
use App\Repositories\Interfaces\Comment\CommentRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CommentRepository implements CommentRepositoryInterface
{
    protected $apiController;

    public function __construct(BaseController $apiController)
    {
        $this->apiController = $apiController;
    }

    public function getAll($request)
    {
        $data_item = Comment::first();
        $columns = $data_item ? array_keys($data_item->toArray()) : [];
        $queryFilter = setQueryList($request, $columns);

        $results = Comment::select('*')
            ->whereRaw($queryFilter["queryKey"], $queryFilter["queryVal"])
            ->WhereRaw($queryFilter["querySearchKey"], $queryFilter["querySearchVal"])
            ->orderBy($request->orderKey ?? "id", $request->orderBy ?? "asc")
            ->paginate($request->limit ?? 10);

        if ($results) {
            return $this->apiController->trueResult("Data additional menu berhasil di temukan", (object) ["data" => CommentResource::collection($results), "pagination" => setPagination($results)]);
        } else {
            return $this->apiController->falseResult("Data additional menu gagal di ambil", null);
        }
    }

    public function getById($id)
    {

        $result = Comment::find($id);
        if ($result) {
            return $this->apiController->trueResult("Data additional menu berhasil di temukan", (object) ["data" => new CommentResource($result), "pagination" => null]);
        } else {
            return $this->apiController->falseResult("Data additional menu gagal di temukan", null);
        }
    }

    public function create($request)
    {
        $latestData = Comment::withTrashed()->orderBy('id', 'desc')->latest()->first();
        $code = generateCode($latestData !== null ? $latestData->code : null, Comment::CODE);

        $input = $request->all();
        $input['code'] = $code;

        $result = Comment::create($input);

        if ($result) {
            return $this->apiController->trueResult("Data additional menu berhasil di simpan", (object) ["data" => new CommentResource($result), "pagination" => null]);
        } else {
            return $this->apiController->falseResult("Data additional menu gagal di simpan", null);
        }
    }

    public function update($request, $id)
    {
        try {
            DB::beginTransaction();
            $result = Comment::find($id);

            if ($result) {
                $result->image_store_id = $request->image_store_id;
                $result->comment = $request->comment;

                if ($result->isClean()) {
                    return $this->apiController->falseResult("Tidak ada perubahan data yang anda masukan", null);
                }

                $result->save();

                if ($result) {
                    DB::commit();
                    return $this->apiController->trueResult("Data additinal menu berhasil di update", (object) ["data" => new CommentResource($result), "pagination" => null]);
                } else {
                    DB::rollBack();
                    return $this->apiController->falseResult("Data additinal menu gagal di update", null);
                }
            } else {
                DB::rollBack();
                return $this->apiController->falseResult("Data additinal menu tidak di temukan", null);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->apiController->falseResult("Request error", null);
        }
    }

    public function delete($id)
    {
        try {
            DB::beginTransaction();
            $result = Comment::find($id);

            if ($result) {
                $result->delete();

                if ($result) {
                    deleteFileInS3($result->icon_url);
                    DB::commit();
                    return $this->apiController->trueResult("Data additional menu berhasil di hapus", (object) ["data" => new CommentResource($result), "pagination" => null]);
                } else {
                    DB::rollBack();
                    return $this->apiController->falseResult("Data additional menu gagal di hapus", null);
                }
            } else {
                DB::rollBack();
                return $this->apiController->falseResult("Data additional menu tidak di temukan", null);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->apiController->falseResult("Request error", null);
        }
    }
}
