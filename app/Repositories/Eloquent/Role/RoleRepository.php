<?php

namespace App\Repositories\Eloquent\Role;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Role\RoleResource;
use App\Models\Role;
use App\Repositories\Interfaces\Role\RoleRepositoryInterface;

class RoleRepository implements RoleRepositoryInterface
{
    protected $apiController;

    public function __construct(BaseController $apiController)
    {
        $this->apiController = $apiController;
    }

    public function getAll($request)
    {
        $data_item = Role::first();
        $columns = $data_item ? array_keys($data_item->toArray()) : [];
        $queryFilter = setQueryList($request, $columns);

        $results = Role::select('*')
            ->whereRaw($queryFilter["queryKey"], $queryFilter["queryVal"])
            ->WhereRaw($queryFilter["querySearchKey"], $queryFilter["querySearchVal"])
            ->orderBy($request->orderKey ?? "id", $request->orderBy ?? "asc")
            ->paginate($request->limit ?? 10);

        if ($results) {
            return $this->apiController->trueResult("Data role berhasil di temukan", (object) ["data" => RoleResource::collection($results), "pagination" => setPagination($results)]);
        } else {
            return $this->apiController->falseResult("Data role gagal di ambil", null);
        }
    }

    public function getById($id)
    {

        $result = Role::find($id);
        if ($result) {
            return $this->apiController->trueResult("Data role berhasil di temukan", (object) ["data" => new RoleResource($result), "pagination" => null]);
        } else {
            return $this->apiController->falseResult("Data role gagal di temukan", null);
        }
    }

    public function create($request)
    {
        $latestData = Role::withTrashed()->orderBy('id', 'desc')->latest()->first();
        $code = generateCode($latestData !== null ? $latestData->role_code : null, Role::CODE);

        $input = $request->all();
        $input['role_code'] = $code;

        $result = Role::create($input);

        if ($result) {
            return $this->apiController->trueResult("Data role berhasil di simpan", (object) ["data" => new RoleResource($result), "pagination" => null]);
        } else {
            return $this->apiController->falseResult("Data role gagal di simpan", null);
        }
    }

    public function update($request, $id)
    {
        $result = Role::find($id);

        if ($result) {
            $result->description = $request->description;

            if ($result->isClean()) {
                return $this->apiController->falseResult("Tidak ada perubahan data yang anda masukan", null);
            }

            $result->save();

            if ($result) {
                return $this->apiController->trueResult("Data role berhasil di update", (object) ["data" => new RoleResource($result), "pagination" => null]);
            } else {
                return $this->apiController->falseResult("Data role gagal di update", null);
            }
        } else {
            return $this->apiController->falseResult("Data role tidak di temukan", null);
        }
    }

    public function delete($id)
    {
        $result = Role::find($id);

        if ($result) {
            $result->delete();

            if ($result) {
                return $this->apiController->trueResult("Data role berhasil di hapus", (object) ["data" => new RoleResource($result), "pagination" => null]);
            } else {
                return $this->apiController->falseResult("Data role gagal di hapus", null);
            }
        } else {
            return $this->apiController->falseResult("Data role tidak di temukan", null);
        }
    }
}
