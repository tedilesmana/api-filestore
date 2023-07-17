<?php

namespace App\Repositories\Eloquent\GatewayFeature;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Global\GlobarResource;
use App\Models\Application;
use App\Models\Feature;
use App\Models\Module;
use App\Repositories\Interfaces\GatewayFeature\GatewayFeatureRepositoryInterface;
use App\Services\MessageGatewayService;
use Illuminate\Support\Str;

class GatewayFeatureRepository implements GatewayFeatureRepositoryInterface
{
    protected $apiController;
    protected $messageGatewayService;

    public function __construct(BaseController $apiController, MessageGatewayService $messageGatewayService)
    {
        $this->apiController = $apiController;
        $this->messageGatewayService = $messageGatewayService;
    }

    public function getAll($request)
    {
        $data_item = Feature::first();
        $columns = $data_item ? array_keys($data_item->toArray()) : [];
        $queryFilter = setQueryList($request, $columns);

        $results = Feature::select('*')
            ->whereRaw($queryFilter["queryKey"], $queryFilter["queryVal"])
            ->WhereRaw($queryFilter["querySearchKey"], $queryFilter["querySearchVal"])
            ->orderBy($request->orderKey ?? "id", $request->orderBy ?? "asc")
            ->paginate($request->limit ?? 10);

        if ($results) {
            return $this->apiController->trueResult("Data feature berhasil di temukan", (object) ["data" => GlobarResource::collection($results), "pagination" => setPagination($results)]);
        } else {
            return $this->apiController->falseResult("Data feature gagal di ambil", null);
        }
    }

    public function getById($id)
    {
        $results = Feature::find($id);

        if ($results) {
            return $this->apiController->trueResult("Data feature berhasil di temukan", (object) ["data" => $results, "pagination" => null]);
        } else {
            return $this->apiController->falseResult("Data feature gagal di ambil", null);
        }
    }

    public function create($request)
    {
        $module = Module::find($request->module_id);
        $application = Application::find($module->application_id);
        $input = $request->all();
        $input["slug"] = Str::slug(strlen($application->id) == 1 ? '0' . $application->id : $application->id) . '-' . Str::slug($request->name);
        $result = Feature::create($input);

        if ($result) {
            return $this->apiController->trueResult("Data feature berhasil di buat", (object) ["data" => $result, "pagination" => null]);
        } else {
            return $this->apiController->falseResult("Data feature gagal di buat", null);
        }
    }

    public function update($request, $id)
    {
        $module = Module::find($request->module_id);
        $application = Application::find($module->application_id);
        $feature = Feature::find($id);

        if ($feature) {
            $feature->name = $request->name;
            $feature->description = $request->description;
            $feature->module_id = $request->module_id;
            $feature->slug = Str::slug(strlen($application->id) == 1 ? '0' . $application->id : $application->id) . '-' . Str::slug($request->name);

            if ($feature->isClean()) {
                return $this->apiController->falseResult('Tidak ada perubahan data yang anda masukan', null);
            }

            $feature->save();

            if ($feature) {
                return $this->apiController->trueResult("Data feature berhasil di update", (object) ["data" => $feature, "pagination" => null]);
            } else {
                return $this->apiController->falseResult("Data feature gagal di update", null);
            }
        } else {
            return $this->apiController->falseResult("Data feature gagal di update", null);
        }
    }

    public function delete($id)
    {
        $feature = Feature::find($id);

        if ($feature) {
            $feature->delete();

            if ($feature) {
                return $this->apiController->trueResult("Data feature berhasil di hapus", (object) ["data" => $feature, "pagination" => null]);
            } else {
                return $this->apiController->falseResult("Data feature gagal di hapus", null);
            }
        } else {
            return $this->apiController->falseResult("Data feature gagal di hapus", null);
        }
    }
}
