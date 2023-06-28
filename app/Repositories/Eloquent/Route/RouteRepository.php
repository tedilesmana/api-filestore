<?php

namespace App\Repositories\Eloquent\Route;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Menu\RouteResource;
use App\Models\Route;
use App\Repositories\Interfaces\Route\RouteRepositoryInterface;

class RouteRepository implements RouteRepositoryInterface
{
    protected $apiController;

    public function __construct(BaseController $apiController)
    {
        $this->apiController = $apiController;
    }

    public function getAll($request)
    {
        $data_item = Route::first();
        $columns = $data_item ? array_keys($data_item->toArray()) : [];
        $queryFilter = setQueryList($request, $columns);

        $results = Route::select('*')
            ->whereRaw($queryFilter["queryKey"], $queryFilter["queryVal"])
            ->WhereRaw($queryFilter["querySearchKey"], $queryFilter["querySearchVal"])
            ->orderBy($request->orderKey ?? "id", $request->orderBy ?? "asc")
            ->paginate($request->limit ?? 10);

        if ($results) {
            return $this->apiController->trueResult("Data route berhasil di temukan", (object) ["data" => RouteResource::collection($results), "pagination" => setPagination($results)]);
        } else {
            return $this->apiController->falseResult("Data route gagal di ambil", null);
        }
    }

    public function getById($id)
    {

        $result = Route::find($id);
        if ($result) {
            return $this->apiController->trueResult("Data route berhasil di temukan", (object) ["data" => new RouteResource($result), "pagination" => null]);
        } else {
            return $this->apiController->falseResult("Data route gagal di temukan", null);
        }
    }

    public function create($request)
    {
        $latestData = Route::withTrashed()->orderBy('id', 'desc')->latest()->first();
        $code = generateCode($latestData !== null ? $latestData->route_code : null, Route::CODE);

        $input = $request->all();
        $input['route_code'] = $code;

        $result = Route::create($input);

        if ($result) {
            return $this->apiController->trueResult("Data route berhasil di simpan", (object) ["data" => new RouteResource($result), "pagination" => null]);
        } else {
            return $this->apiController->falseResult("Data route gagal di simpan", null);
        }
    }

    public function update($request, $id)
    {
        $result = Route::find($id);

        if ($result) {
            $result->name = $request->name;
            $result->title = $request->title;
            $result->sub_title = $request->sub_title;
            $result->path = $request->path;
            $result->icon_url = $request->icon_url;
            $result->access_permissions = $request->access_permissions;

            if ($result->isClean()) {
                return $this->apiController->falseResult("Tidak ada perubahan data yang anda masukan", null);
            }

            $result->save();

            if ($result) {
                return $this->apiController->trueResult("Data route berhasil di update", (object) ["data" => new RouteResource($result), "pagination" => null]);
            } else {
                return $this->apiController->falseResult("Data route gagal di update", null);
            }
        } else {
            return $this->apiController->falseResult("Data route tidak di temukan", null);
        }
    }

    public function delete($id)
    {
        $result = Route::find($id);

        if ($result) {
            $result->delete();

            if ($result) {
                return $this->apiController->trueResult("Data route berhasil di hapus", (object) ["data" => new RouteResource($result), "pagination" => null]);
            } else {
                return $this->apiController->falseResult("Data route gagal di hapus", null);
            }
        } else {
            return $this->apiController->falseResult("Data route tidak di temukan", null);
        }
    }
}
