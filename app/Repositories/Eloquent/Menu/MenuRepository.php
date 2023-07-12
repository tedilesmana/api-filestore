<?php

namespace App\Repositories\Eloquent\Menu;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Menu\MenuResource;
use App\Models\Menu;
use App\Repositories\Interfaces\Menu\MenuRepositoryInterface;
use Illuminate\Support\Facades\DB;

class MenuRepository implements MenuRepositoryInterface
{
    protected $apiController;

    public function __construct(BaseController $apiController)
    {
        $this->apiController = $apiController;
    }

    public function getAll($request)
    {
        $data_item = Menu::first();
        $columns = $data_item ? array_keys($data_item->toArray()) : [];
        $queryFilter = setQueryList($request, $columns);

        $results = Menu::select('*')
            ->whereRaw($queryFilter["queryKey"], $queryFilter["queryVal"])
            ->WhereRaw($queryFilter["querySearchKey"], $queryFilter["querySearchVal"])
            ->orderBy($request->orderKey ?? "id", $request->orderBy ?? "asc")
            ->paginate($request->limit ?? 10);

        if ($results) {
            return $this->apiController->trueResult("Data menu berhasil di temukan", (object) ["data" => MenuResource::collection($results), "pagination" => setPagination($results)]);
        } else {
            return $this->apiController->falseResult("Data menu gagal di ambil", null);
        }
    }

    public function getById($id)
    {

        $result = Menu::find($id);
        if ($result) {
            return $this->apiController->trueResult("Data menu berhasil di temukan", (object) ["data" => new MenuResource($result), "pagination" => null]);
        } else {
            return $this->apiController->falseResult("Data menu gagal di temukan", null);
        }
    }

    public function create($request)
    {
        $latestData = Menu::withTrashed()->orderBy('id', 'desc')->latest()->first();
        $code = generateCode($latestData !== null ? $latestData->menu_code : null, Menu::CODE);

        $input = $request->all();
        $input['menu_code'] = $code;

        $result = Menu::create($input);

        if ($result) {
            return $this->apiController->trueResult("Data menu berhasil di simpan", (object) ["data" => new MenuResource($result), "pagination" => null]);
        } else {
            return $this->apiController->falseResult("Data menu gagal di simpan", null);
        }
    }

    public function update($request, $id)
    {
        try {
            DB::beginTransaction();
            $result = Menu::find($id);
            $oldImage = $result->icon_url;

            if ($result) {
                $input['route_id'] = $request->route_id;
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
                    if ($request->icon_url != $oldImage) {
                        deleteFileInS3($request->image_url);
                    };
                    DB::commit();
                    return $this->apiController->trueResult("Data menu berhasil di update", (object) ["data" => new MenuResource($result), "pagination" => null]);
                } else {
                    DB::rollBack();
                    return $this->apiController->falseResult("Data menu gagal di update", null);
                }
            } else {
                DB::rollBack();
                return $this->apiController->falseResult("Data menu tidak di temukan", null);
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
            $result = Menu::find($id);

            if ($result) {
                $result->delete();

                if ($result) {
                    deleteFileInS3($result->icon_url);
                    DB::commit();
                    return $this->apiController->trueResult("Data menu berhasil di hapus", (object) ["data" => new MenuResource($result), "pagination" => null]);
                } else {
                    DB::rollBack();
                    return $this->apiController->falseResult("Data menu gagal di hapus", null);
                }
            } else {
                DB::rollBack();
                return $this->apiController->falseResult("Data menu tidak di temukan", null);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->apiController->falseResult("Request error", null);
        }
    }
}
