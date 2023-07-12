<?php

namespace App\Repositories\Eloquent\GatewayModule;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Global\GlobarResource;
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
        $result = Module::where("application_id", $id)->get();

        if ($result) {
            return $this->apiController->trueResult("Data module berhasil di ambil", (object) ["data" => $result, "pagination" => null]);
        } else {
            return $this->apiController->falseResult("Data module gagal di ambil", null);
        }
    }

    public function create($request)
    {
        $input = $request->all();
        $result = Module::create($input);

        if ($result) {
            return $this->apiController->trueResult("Data module berhasil di buat", (object) ["data" => $result, "pagination" => null]);
        } else {
            return $this->apiController->falseResult("Data module gagal di buat", null);
        }
    }

    public function update($request, $id)
    {
        $application = Module::find($id);

        if ($application) {
            $application->name = $request->name;
            $application->description = $request->description;
            $application->application_id = $request->application_id;
            $application->slug = Str::slug($request->name);

            if ($application->isClean()) {
                return $this->apiController->falseResult('Tidak ada perubahan data yang anda masukan', null);
            }

            $application->save();

            if ($application) {
                return $this->apiController->trueResult("Data module berhasil di update", (object) ["data" => $application, "pagination" => null]);
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
