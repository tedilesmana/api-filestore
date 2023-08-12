<?php

namespace App\Repositories\Eloquent\Category;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Category\CategoryResource;
use App\Models\Category;
use App\Repositories\Interfaces\Category\CategoryRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CategoryRepository implements CategoryRepositoryInterface
{
    protected $apiController;

    public function __construct(BaseController $apiController)
    {
        $this->apiController = $apiController;
    }

    public function getAll($request)
    {
        $data_item = Category::first();
        $columns = $data_item ? array_keys($data_item->toArray()) : [];
        $queryFilter = setQueryList($request, $columns);

        $results = Category::select('*')
            ->whereRaw($queryFilter["queryKey"], $queryFilter["queryVal"])
            ->WhereRaw($queryFilter["querySearchKey"], $queryFilter["querySearchVal"])
            ->orderBy($request->orderKey ?? "id", $request->orderBy ?? "asc")
            ->paginate($request->limit ?? 10);

        if ($results) {
            return $this->apiController->trueResult("Data category berhasil di temukan", (object) ["data" => CategoryResource::collection($results), "pagination" => setPagination($results)]);
        } else {
            return $this->apiController->falseResult("Data category gagal di ambil", null);
        }
    }

    public function getById($id)
    {

        $result = Category::find($id);
        if ($result) {
            return $this->apiController->trueResult("Data category berhasil di temukan", (object) ["data" => new CategoryResource($result), "pagination" => null]);
        } else {
            return $this->apiController->falseResult("Data category gagal di temukan", null);
        }
    }

    public function create($request)
    {
        $latestData = Category::withTrashed()->orderBy('id', 'desc')->latest()->first();
        $code = generateCode($latestData !== null ? $latestData->code : null, Category::CODE);

        $input = $request->all();
        $input['code'] = $code;

        $result = Category::create($input);

        if ($result) {
            return $this->apiController->trueResult("Data category berhasil di simpan", (object) ["data" => new CategoryResource($result), "pagination" => null]);
        } else {
            return $this->apiController->falseResult("Data category gagal di simpan", null);
        }
    }

    public function update($request, $id)
    {
        try {
            DB::beginTransaction();
            $result = Category::find($id);

            if ($result) {
                $result->name = $request->name;

                if ($result->isClean()) {
                    return $this->apiController->falseResult("Tidak ada perubahan data yang anda masukan", null);
                }

                $result->save();

                if ($result) {
                    DB::commit();
                    return $this->apiController->trueResult("Data category berhasil di update", (object) ["data" => new CategoryResource($result), "pagination" => null]);
                } else {
                    DB::rollBack();
                    return $this->apiController->falseResult("Data category gagal di update", null);
                }
            } else {
                DB::rollBack();
                return $this->apiController->falseResult("Data category tidak di temukan", null);
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
            $result = Category::find($id);

            if ($result) {
                $result->delete();

                if ($result) {
                    DB::commit();
                    return $this->apiController->trueResult("Data category berhasil di hapus", (object) ["data" => new CategoryResource($result), "pagination" => null]);
                } else {
                    DB::rollBack();
                    return $this->apiController->falseResult("Data category gagal di hapus", null);
                }
            } else {
                DB::rollBack();
                return $this->apiController->falseResult("Data category tidak di temukan", null);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->apiController->falseResult("Request error", null);
        }
    }
}
