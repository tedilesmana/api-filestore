<?php

namespace App\Repositories\Eloquent\ImageStore;

use App\Http\Controllers\BaseController;
use App\Http\Resources\ImageStore\ImageStoreResource;
use App\Models\ImageStore;
use App\Repositories\Interfaces\ImageStore\ImageStoreRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ImageStoreRepository implements ImageStoreRepositoryInterface
{
    protected $apiController;

    public function __construct(BaseController $apiController)
    {
        $this->apiController = $apiController;
    }

    public function getAll($request)
    {
        $data_item = ImageStore::first();
        $columns = $data_item ? array_keys($data_item->toArray()) : [];
        $queryFilter = setQueryList($request, $columns);

        $results = ImageStore::select('*')
            ->whereRaw($queryFilter["queryKey"], $queryFilter["queryVal"])
            ->WhereRaw($queryFilter["querySearchKey"], $queryFilter["querySearchVal"])
            ->with('category')
            ->with('user')
            ->orderBy($request->orderKey ?? "id", $request->orderBy ?? "asc")
            ->paginate($request->limit ?? 10);

        if ($results) {
            return $this->apiController->trueResult("Data gambar berhasil di temukan", (object) ["data" => ImageStoreResource::collection($results), "pagination" => setPagination($results)]);
        } else {
            return $this->apiController->falseResult("Data gambar gagal di ambil", null);
        }
    }

    public function getById($id)
    {
        $result = ImageStore::find($id);
        if ($result) {
            return $this->apiController->trueResult("Data gambar berhasil di temukan", (object) ["data" => new ImageStoreResource($result), "pagination" => null]);
        } else {
            return $this->apiController->falseResult("Data gambar gagal di temukan", null);
        }
    }

    public function create($request)
    {
        $latestData = ImageStore::withTrashed()->orderBy('id', 'desc')->latest()->first();
        $code = generateCode($latestData !== null ? $latestData->code : null, ImageStore::CODE);

        $input = $request->all();
        $input['code'] = $code;

        $result = ImageStore::create($input);

        if ($result) {
            return $this->apiController->trueResult("Data gambar berhasil di simpan", (object) ["data" => new ImageStoreResource($result), "pagination" => null]);
        } else {
            return $this->apiController->falseResult("Data gambar gagal di simpan", null);
        }
    }

    public function update($request, $id)
    {
        try {
            DB::beginTransaction();
            $result = ImageStore::find($id);

            if ($result) {
                $result->category_id = $request->category_id;
                $result->user_id = $request->user_id;
                $result->name = $request->name;
                $result->description = $request->description;
                $result->filename = $request->filename;
                $result->extention = $request->extention;
                $result->size = $request->size;
                $result->directory = $request->directory;
                $result->image_url = $request->image_url;

                if ($result->isClean()) {
                    return $this->apiController->falseResult("Tidak ada perubahan data yang anda masukan", null);
                }

                $result->save();

                if ($result) {
                    DB::commit();
                    return $this->apiController->trueResult("Data additinal menu berhasil di update", (object) ["data" => new ImageStoreResource($result), "pagination" => null]);
                } else {
                    DB::rollBack();
                    return $this->apiController->falseResult("Data additinal menu gagal di update", null);
                }
            } else {
                DB::rollBack();
                return $this->apiController->falseResult("Data additinal menu tidak di temukan", null);
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
            $result = ImageStore::find($id);

            if ($result) {
                $result->delete();

                if ($result) {
                    DB::commit();
                    return $this->apiController->trueResult("Data gambar berhasil di hapus", (object) ["data" => new ImageStoreResource($result), "pagination" => null]);
                } else {
                    DB::rollBack();
                    return $this->apiController->falseResult("Data gambar gagal di hapus", null);
                }
            } else {
                DB::rollBack();
                return $this->apiController->falseResult("Data gambar tidak di temukan", null);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->apiController->falseResult("Request error", null);
        }
    }

    public function getTotalImageByCategory()
    {
        $result = ImageStore::select('category_id', DB::raw('count(*) as total'))
            ->with('category')
            ->groupBy('category_id')
            ->get();

        if ($result) {
            return $this->apiController->trueResult("Data gambar berhasil di temukan", (object) ["data" => new ImageStoreResource($result), "pagination" => null]);
        } else {
            return $this->apiController->falseResult("Data gambar gagal di temukan", null);
        }
    }
}
