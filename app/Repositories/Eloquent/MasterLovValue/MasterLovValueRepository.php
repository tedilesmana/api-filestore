<?php

namespace App\Repositories\Eloquent\MasterLovValue;

use App\Http\Controllers\BaseController;
use App\Http\Resources\MasterLov\MasterLovValueResource;
use App\Models\MasterLovValue;
use App\Repositories\Interfaces\MasterLovValue\MasterLovValueRepositoryInterface;

class MasterLovValueRepository implements MasterLovValueRepositoryInterface
{
    protected $apiController;

    public function __construct(BaseController $apiController)
    {
        $this->apiController = $apiController;
    }

    public function getAll($request)
    {
        $data_item = MasterLovValue::first();
        $columns = $data_item ? array_keys($data_item->toArray()) : [];
        $queryFilter = setQueryList($request, $columns);

        $results = MasterLovValue::select('*')
            ->whereRaw($queryFilter["queryKey"], $queryFilter["queryVal"])
            ->WhereRaw($queryFilter["querySearchKey"], $queryFilter["querySearchVal"])
            ->orderBy($request->orderKey ?? "id", $request->orderBy ?? "asc")
            ->paginate($request->limit ?? 10);

        if ($results) {
            return $this->apiController->trueResult("Data master lov value berhasil di temukan", (object) ["data" => MasterLovValueResource::collection($results), "pagination" => setPagination($results)]);
        } else {
            return $this->apiController->falseResult("Data master lov value gagal di ambil", null);
        }
    }

    public function getById($id)
    {

        $result = MasterLovValue::find($id);
        if ($result) {
            return $this->apiController->trueResult("Data master lov value berhasil di temukan", (object) ["data" => new MasterLovValueResource($result), "pagination" => null]);
        } else {
            return $this->apiController->falseResult("Data master lov value gagal di temukan", null);
        }
    }

    public function create($request)
    {
        $input = $request->all();

        $result = MasterLovValue::create($input);

        if ($result) {
            return $this->apiController->trueResult("Data master lov value berhasil di simpan", (object) ["data" => new MasterLovValueResource($result), "pagination" => null]);
        } else {
            return $this->apiController->falseResult("Data master lov value gagal di simpan", null);
        }
    }

    public function update($request, $id)
    {
        $result = MasterLovValue::find($id);

        if ($result) {
            $result->master_lov_group_id = $request->master_lov_group_id;
            $result->group_name = $request->group_name;
            $result->values = $request->values;

            if ($result->isClean()) {
                return $this->apiController->falseResult("Tidak ada perubahan data yang anda masukan", null);
            }

            $result->save();

            if ($result) {
                return $this->apiController->trueResult("Data master lov value berhasil di update", (object) ["data" => new MasterLovValueResource($result), "pagination" => null]);
            } else {
                return $this->apiController->falseResult("Data master lov value gagal di update", null);
            }
        } else {
            return $this->apiController->falseResult("Data master lov value tidak di temukan", null);
        }
    }

    public function delete($id)
    {
        $result = MasterLovValue::find($id);

        if ($result) {
            $result->delete();

            if ($result) {
                return $this->apiController->trueResult("Data master lov value berhasil di hapus", (object) ["data" => new MasterLovValueResource($result), "pagination" => null]);
            } else {
                return $this->apiController->falseResult("Data master lov value gagal di hapus", null);
            }
        } else {
            return $this->apiController->falseResult("Data master lov value tidak di temukan", null);
        }
    }
}
