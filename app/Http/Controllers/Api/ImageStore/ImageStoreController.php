<?php

namespace App\Http\Controllers\Api\ImageStore;

use App\Http\Controllers\BaseController;
use App\Http\Requests\ImageStore\ImageStoreRequest;
use App\Repositories\Interfaces\ImageStore\ImageStoreRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImageStoreController extends BaseController
{
    protected $eloquentRepository;
    public function __construct(ImageStoreRepositoryInterface $eloquentRepository)
    {
        $this->eloquentRepository = $eloquentRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $response = $this->eloquentRepository->getAll($request);
            if ($response->success) {
                return $this->successResponse($response->message, $response->data->data, $response->data->pagination);
            } else {
                return $this->errorResponse($response->message, $response->data);
            }
        } catch (\Exception $e) {
            return $this->badResponse($e->getMessage(), null);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ImageStoreRequest $request)
    {
        try {
            $response = $this->eloquentRepository->create($request);
            if ($response->success) {
                return $this->successResponse($response->message, $response->data->data, $response->data->pagination);
            } else {
                return $this->errorResponse($response->message, $response->data);
            }
        } catch (\Exception $e) {
            return $this->badResponse($e->getMessage(), null);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $response = $this->eloquentRepository->getById($id);
            if ($response->success) {
                return $this->successResponse($response->message, $response->data->data, $response->data->pagination);
            } else {
                return $this->errorResponse($response->message, $response->data);
            }
        } catch (\Exception $e) {
            return $this->badResponse($e->getMessage(), null);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $response = $this->eloquentRepository->getById($id);
            if ($response->success) {
                return $this->successResponse($response->message, $response->data->data, $response->data->pagination);
            } else {
                return $this->errorResponse($response->message, $response->data);
            }
        } catch (\Exception $e) {
            return $this->badResponse($e->getMessage(), null);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ImageStoreRequest $request, $id)
    {
        try {
            $response = $this->eloquentRepository->update($request, $id);
            if ($response->success) {
                return $this->successResponse($response->message, $response->data->data, $response->data->pagination);
            } else {
                return $this->errorResponse($response->message, $response->data);
            }
        } catch (\Exception $e) {
            return $this->badResponse($e->getMessage(), null);
        }
    }

    public function getTotalImageByCategory()
    {
        try {
            $response = $this->eloquentRepository->getTotalImageByCategory();
            if ($response->success) {
                return $this->successResponse($response->message, $response->data->data, $response->data->pagination);
            } else {
                return $this->errorResponse($response->message, $response->data);
            }
        } catch (\Exception $e) {
            return $this->badResponse($e->getMessage(), null);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        try {
            if (is_null($request->list_id)) {
                $response = $this->eloquentRepository->delete($id);
                if ($response->success) {
                    return $this->successResponse($response->message, $response->data->data, $response->data->pagination);
                } else {
                    return $this->errorResponse($response->message, $response->data);
                }
            } else {
                $category = DB::table("image_stores")->whereIn('id', $request->list_id)->delete();

                if ($category) {
                    return $this->successResponse("Delete data berhasil",  $category);
                } else {
                    return $this->errorResponse('Data tidak ditemukan', null);
                }
            }
        } catch (\Exception $e) {
            return $this->badResponse($e->getMessage(), null);
        }
    }
}
