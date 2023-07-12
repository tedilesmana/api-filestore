<?php

namespace App\Repositories\Eloquent\GatewayApplication;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Global\GlobarResource;
use App\Models\Application;
use App\Repositories\Interfaces\GatewayApplication\GatewayApplicationRepositoryInterface;
use App\Services\MessageGatewayService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class GatewayApplicationRepository implements GatewayApplicationRepositoryInterface
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
        $data_item = Application::first();
        $columns = $data_item ? array_keys($data_item->toArray()) : [];
        $queryFilter = setQueryList($request, $columns);

        $results = Application::select('*')
            ->whereRaw($queryFilter["queryKey"], $queryFilter["queryVal"])
            ->WhereRaw($queryFilter["querySearchKey"], $queryFilter["querySearchVal"])
            ->orderBy($request->orderKey ?? "id", $request->orderBy ?? "asc")
            ->paginate($request->limit ?? 10);

        if ($results) {
            return $this->apiController->trueResult("Data applikasi berhasil di temukan", (object) ["data" => GlobarResource::collection($results), "pagination" => setPagination($results)]);
        } else {
            return $this->apiController->falseResult("Data applikasi gagal di ambil", null);
        }
    }

    public function getById($id)
    {
        $results = Application::find($id);

        if ($results) {
            return $this->apiController->trueResult("Data applikasi berhasil di temukan", (object) ["data" => $results, "pagination" => null]);
        } else {
            return $this->apiController->falseResult("Data applikasi gagal di ambil", null);
        }
    }

    public function create($request)
    {
        $input = $request->all();
        $results = Application::create($input); 

        if ($results) {
            return $this->apiController->trueResult("Data applikasi berhasil di buat", (object) ["data" => $results, "pagination" => null]);
        } else {
            return $this->apiController->falseResult("Data applikasi gagal di buat", null);
        }
    }

    public function update($request, $id)
    {
        $application = Application::find($id);

        if ($application) {
            $application->name = $request->name;
            $application->description = $request->description;
            $application->base_url = $request->base_url;
            $application->image_url = $request->image_url;
            $application->slug = Str::slug($request->name);

            if ($application->isClean()) {
                return $this->apiController->falseResult('Tidak ada perubahan data yang anda masukan', null);
            }

            $application->save();

            if ($application) {
                return $this->apiController->trueResult("Data applikasi berhasil di update", (object) ["data" => $application, "pagination" => null]);
            } else {
                return $this->apiController->falseResult("Data applikasi gagal di update", null);
            }
        } else {
            return $this->apiController->falseResult("Data applikasi gagal di update", null);
        }
    }

    public function delete($id)
    {
        $application = Application::find($id);

        if ($application) {
            Schema::dropIfExists($application->slug);
            $application->delete();

            if ($application) {
                return $this->apiController->trueResult("Data applikasi berhasil di hapus", (object) ["data" => $application, "pagination" => null]);
            } else {
                return $this->apiController->falseResult("Data applikasi gagal di hapus", null);
            }
        } else {
            return $this->apiController->falseResult("Data applikasi gagal di hapus", null);
        }
    }
}
