<?php

namespace App\Repositories\Eloquent\GatewayModule;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Global\GlobarResource;
use App\Models\Application;
use App\Models\Module;
use App\Repositories\Interfaces\GatewayModule\GatewayModuleRepositoryInterface;
use App\Services\MessageGatewayService;
use Illuminate\Support\Str;

class GatewayModuleRepository implements GatewayModuleRepositoryInterface
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

        $data_item = Module::first();
        $columns = $data_item ? array_keys($data_item->toArray()) : [];
        $queryFilter = setQueryList($request, $columns);

        $results = Module::select('*')
            ->whereRaw($queryFilter["queryKey"], $queryFilter["queryVal"])
            ->WhereRaw($queryFilter["querySearchKey"], $queryFilter["querySearchVal"])
            ->orderBy($request->orderKey ?? "id", $request->orderBy ?? "asc")
            ->paginate($request->limit ?? 10);

        if ($results) {
            return $this->apiController->trueResult("Data additional menu berhasil di temukan", (object) ["data" => GlobarResource::collection($results), "pagination" => setPagination($results)]);
        } else {
            return $this->apiController->falseResult("Data additional menu gagal di ambil", null);
        }
    }

    public function getById($id)
    {
        $results = Module::find($id);

        if ($results) {
            return $this->apiController->trueResult("Data module berhasil di temukan", (object) ["data" => $results, "pagination" => null]);
        } else {
            return $this->apiController->falseResult("Data module gagal di ambil", null);
        }
    }

    public function create($request)
    {
        $application = Application::find($request->application_id);
        $input = $request->all();
        $input["slug"] = Str::slug(strlen($application->id) == 1 ? '0' . $application->id : $application->id) . '-' . Str::slug($request->name);
        $result = Module::create($input);

        if ($result) {
            return $this->apiController->trueResult("Data module berhasil di buat", (object) ["data" => $result, "pagination" => null]);
        } else {
            return $this->apiController->falseResult("Data module gagal di buat", null);
        }
    }

    public function update($request, $id)
    {
        $application = Application::find($request->application_id);
        $module = Module::find($id);

        if ($module) {
            $module->name = $request->name;
            $module->description = $request->description;
            $module->application_id = $request->application_id;
            $module->slug = Str::slug(strlen($application->id) == 1 ? '0' . $application->id : $application->id) . '-' . Str::slug($request->name);

            if ($module->isClean()) {
                return $this->apiController->falseResult('Tidak ada perubahan data yang anda masukan', null);
            }

            $module->save();

            if ($module) {
                return $this->apiController->trueResult("Data module berhasil di update", (object) ["data" => $module, "pagination" => null]);
            } else {
                return $this->apiController->falseResult("Data module gagal di update", null);
            }
        } else {
            return $this->apiController->falseResult("Data modul gagal di update", null);
        }
    }

    public function delete($id)
    {
        $application = Module::find($id);

        if ($application) {
            $application->delete();

            if ($application) {
                return $this->apiController->trueResult("Data module berhasil di hapus", (object) ["data" => $application, "pagination" => null]);
            } else {
                return $this->apiController->falseResult("Data module gagal di hapus", null);
            }
        } else {
            return $this->apiController->falseResult("Data modul gagal di hapus", null);
        }
    }
}
