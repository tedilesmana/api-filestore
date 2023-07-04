<?php

namespace App\Repositories\Eloquent\Permission;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Permission\PermissionResource;
use App\Models\Permission;
use App\Repositories\Interfaces\Permission\PermissionRepositoryInterface;

class PermissionRepository implements PermissionRepositoryInterface
{
    protected $apiController;

    public function __construct(BaseController $apiController)
    {
        $this->apiController = $apiController;
    }

    public function getAll($request)
    {
        $data_item = Permission::first();
        $columns = $data_item ? array_keys($data_item->toArray()) : [];
        $queryFilter = setQueryList($request, $columns);

        $results = Permission::select('*')
            ->whereRaw($queryFilter["queryKey"], $queryFilter["queryVal"])
            ->WhereRaw($queryFilter["querySearchKey"], $queryFilter["querySearchVal"])
            ->orderBy($request->orderKey ?? "id", $request->orderBy ?? "asc")
            ->paginate($request->limit ?? 10);

        if ($results) {
            return $this->apiController->trueResult("Data permission berhasil di temukan", (object) ["data" => PermissionResource::collection($results), "pagination" => setPagination($results)]);
        } else {
            return $this->apiController->falseResult("Data permission gagal di ambil", null);
        }
    }

    public function getById($id)
    {

        $result = Permission::find($id);
        if ($result) {
            return $this->apiController->trueResult("Data permission berhasil di temukan", (object) ["data" => new PermissionResource($result), "pagination" => null]);
        } else {
            return $this->apiController->falseResult("Data permission gagal di temukan", null);
        }
    }

    public function create($request)
    {
        $latestData = Permission::withTrashed()->orderBy('id', 'desc')->latest()->first();
        $code = generateCode($latestData !== null ? $latestData->permission_code : null, Permission::CODE);

        $input = $request->all();
        $input['permission_code'] = $code;

        $result = Permission::create($input);

        if ($result) {
            return $this->apiController->trueResult("Data permission berhasil di simpan", (object) ["data" => new PermissionResource($result), "pagination" => null]);
        } else {
            return $this->apiController->falseResult("Data permission gagal di simpan", null);
        }
    }

    public function update($request, $id)
    {
        $result = Permission::find($id);

        if ($result) {
            $result->description = $request->description;

            if ($result->isClean()) {
                return $this->apiController->falseResult("Tidak ada perubahan data yang anda masukan", null);
            }

            $result->save();

            if ($result) {
                return $this->apiController->trueResult("Data permission berhasil di update", (object) ["data" => new PermissionResource($result), "pagination" => null]);
            } else {
                return $this->apiController->falseResult("Data permission gagal di update", null);
            }
        } else {
            return $this->apiController->falseResult("Data permission tidak di temukan", null);
        }
    }

    public function delete($id)
    {
        $result = Permission::find($id);

        if ($result) {
            $result->delete();

            if ($result) {
                return $this->apiController->trueResult("Data permission berhasil di hapus", (object) ["data" => new PermissionResource($result), "pagination" => null]);
            } else {
                return $this->apiController->falseResult("Data permission gagal di hapus", null);
            }
        } else {
            return $this->apiController->falseResult("Data permission tidak di temukan", null);
        }
    }
}
