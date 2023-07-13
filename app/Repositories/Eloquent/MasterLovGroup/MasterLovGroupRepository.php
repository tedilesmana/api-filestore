<?php

namespace App\Repositories\Eloquent\MasterLovGroup;

use App\Http\Controllers\BaseController;
use App\Http\Resources\MasterLov\MasterLovGroupResource;
use App\Models\MasterLovGroup;
use App\Models\MasterLovValue;
use App\Repositories\Interfaces\MasterLovGroup\MasterLovGroupRepositoryInterface;
use Illuminate\Support\Facades\DB;

class MasterLovGroupRepository implements MasterLovGroupRepositoryInterface
{
    protected $apiController;

    public function __construct(BaseController $apiController)
    {
        $this->apiController = $apiController;
    }

    public function getAll($request)
    {
        $data_item = MasterLovGroup::first();
        $columns = $data_item ? array_keys($data_item->toArray()) : [];
        $queryFilter = setQueryList($request, $columns);

        $results = MasterLovGroup::select('*')
            ->whereRaw($queryFilter["queryKey"], $queryFilter["queryVal"])
            ->WhereRaw($queryFilter["querySearchKey"], $queryFilter["querySearchVal"])
            ->orderBy($request->orderKey ?? "id", $request->orderBy ?? "asc")
            ->paginate($request->limit ?? 10);

        if ($results) {
            return $this->apiController->trueResult("Data master lov group berhasil di temukan", (object) ["data" => MasterLovGroupResource::collection($results), "pagination" => setPagination($results)]);
        } else {
            return $this->apiController->falseResult("Data master lov group gagal di ambil", null);
        }
    }

    public function getById($id)
    {

        $result = MasterLovGroup::find($id);
        if ($result) {
            return $this->apiController->trueResult("Data master lov group berhasil di temukan", (object) ["data" => new MasterLovGroupResource($result), "pagination" => null]);
        } else {
            return $this->apiController->falseResult("Data master lov group gagal di temukan", null);
        }
    }

    public function create($request)
    {
        $input = $request->all();

        $result = MasterLovGroup::create($input);

        if ($result) {
            return $this->apiController->trueResult("Data master lov group berhasil di simpan", (object) ["data" => new MasterLovGroupResource($result), "pagination" => null]);
        } else {
            return $this->apiController->falseResult("Data master lov group gagal di simpan", null);
        }
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        $result = MasterLovGroup::find($id);

        if ($result) {
            MasterLovValue::where('group_name', $result->group_name)->update(['group_name' => $request->group_name]);
            $result->group_name = $request->group_name;

            if ($result->isClean()) {
                return $this->apiController->falseResult("Tidak ada perubahan data yang anda masukan", null);
            }

            $result->save();

            if ($result) {
                DB::commit();
                return $this->apiController->trueResult("Data master lov group berhasil di update", (object) ["data" => new MasterLovGroupResource($result), "pagination" => null]);
            } else {
                DB::rollBack();
                return $this->apiController->falseResult("Data master lov group gagal di update", null);
            }
        } else {
            DB::rollBack();
            return $this->apiController->falseResult("Data master lov group tidak di temukan", null);
        }
    }

    public function delete($id)
    {
        $result = MasterLovGroup::find($id);

        if ($result) {
            $result->delete();

            if ($result) {
                return $this->apiController->trueResult("Data master lov group berhasil di hapus", (object) ["data" => new MasterLovGroupResource($result), "pagination" => null]);
            } else {
                return $this->apiController->falseResult("Data master lov group gagal di hapus", null);
            }
        } else {
            return $this->apiController->falseResult("Data master lov group tidak di temukan", null);
        }
    }
}
