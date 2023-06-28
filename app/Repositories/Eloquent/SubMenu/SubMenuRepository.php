<?php

namespace App\Repositories\Eloquent\SubMenu;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Menu\SubMenuResource;
use App\Models\SubMenu;
use App\Repositories\Interfaces\SubMenu\SubMenuRepositoryInterface;

class SubMenuRepository implements SubMenuRepositoryInterface
{
    protected $apiController;

    public function __construct(BaseController $apiController)
    {
        $this->apiController = $apiController;
    }

    public function getAll($request)
    {
        $data_item = SubMenu::first();
        $columns = $data_item ? array_keys($data_item->toArray()) : [];
        $queryFilter = setQueryList($request, $columns);

        $results = SubMenu::select('*')
            ->whereRaw($queryFilter["queryKey"], $queryFilter["queryVal"])
            ->WhereRaw($queryFilter["querySearchKey"], $queryFilter["querySearchVal"])
            ->orderBy($request->orderKey ?? "id", $request->orderBy ?? "asc")
            ->paginate($request->limit ?? 10);

        if ($results) {
            return $this->apiController->trueResult("Data sub menu berhasil di temukan", (object) ["data" => SubMenuResource::collection($results), "pagination" => setPagination($results)]);
        } else {
            return $this->apiController->falseResult("Data sub menu gagal di ambil", null);
        }
    }

    public function getById($id)
    {

        $result = SubMenu::find($id);
        if ($result) {
            return $this->apiController->trueResult("Data sub menu berhasil di temukan", (object) ["data" => new SubMenuResource($result), "pagination" => null]);
        } else {
            return $this->apiController->falseResult("Data sub menu gagal di temukan", null);
        }
    }

    public function create($request)
    {
        $latestData = SubMenu::withTrashed()->orderBy('id', 'desc')->latest()->first();
        $code = generateCode($latestData !== null ? $latestData->sub_menu_code : null, SubMenu::CODE);

        $input = $request->all();
        $input['sub_menu_code'] = $code;

        $result = SubMenu::create($input);

        if ($result) {
            return $this->apiController->trueResult("Data sub menu berhasil di simpan", (object) ["data" => new SubMenuResource($result), "pagination" => null]);
        } else {
            return $this->apiController->falseResult("Data sub menu gagal di simpan", null);
        }
    }

    public function update($request, $id)
    {
        $result = SubMenu::find($id);

        if ($result) {
            $input['menu_id'] = $request->menu_id;
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
                return $this->apiController->trueResult("Data sub menu berhasil di update", (object) ["data" => new SubMenuResource($result), "pagination" => null]);
            } else {
                return $this->apiController->falseResult("Data sub menu gagal di update", null);
            }
        } else {
            return $this->apiController->falseResult("Data sub menu tidak di temukan", null);
        }
    }

    public function delete($id)
    {
        $result = SubMenu::find($id);

        if ($result) {
            $result->delete();

            if ($result) {
                return $this->apiController->trueResult("Data sub menu berhasil di hapus", (object) ["data" => new SubMenuResource($result), "pagination" => null]);
            } else {
                return $this->apiController->falseResult("Data sub menu gagal di hapus", null);
            }
        } else {
            return $this->apiController->falseResult("Data sub menu tidak di temukan", null);
        }
    }
}
