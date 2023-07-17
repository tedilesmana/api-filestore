<?php

namespace App\Http\Controllers\Api\GatewayManager;

use App\Http\Controllers\BaseController;
use App\Repositories\Interfaces\GatewayManager\GatewayManagerRepositoryInterface;
use App\Services\GlobalApiGatewayService;
use Illuminate\Http\Request;

class GatewayManagerController extends BaseController
{
    private $globalGatewayService;
    protected $gatewayManagerRepository;
    public function __construct(GatewayManagerRepositoryInterface $gatewayManagerRepository, GlobalApiGatewayService $globalGatewayService)
    {
        $this->gatewayManagerRepository = $gatewayManagerRepository;
        $this->globalGatewayService = $globalGatewayService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        //
    }

    public function postRequest($app, $module, $feature, $title, Request $request)
    {
        try {
            $result = $this->gatewayManagerRepository->proceedRequest($app, $module, $feature, $title);
            if ($result->data->methode == "POST") {
                $response = (object) $this->globalGatewayService->globalApiGatewayService($result->data, $request);
                if ($response->success) {
                    return $this->successResponse($response->message, $response->data, isset($response->pagination) ? $response->pagination : null);
                } else {
                    return $this->errorResponse($response->message, $response->data);
                }
            } else {
                return $this->errorResponse("Methode not allow", null);
            }
        } catch (\Exception $e) {
            return $this->badResponse($e->getMessage(), null);
        }
    }

    public function getRequest($app, $module, $feature, $title, Request $request)
    {
        try {
            $result = $this->gatewayManagerRepository->proceedRequest($app, $module, $feature, $title);
            if ($result->data->methode == "GET") {
                $response = (object) $this->globalGatewayService->globalApiGatewayService($result->data, $request);
                if ($response->success) {
                    return $this->successResponse($response->message, $response->data, isset($response->pagination) ? $response->pagination : null);
                } else {
                    return $this->errorResponse($response->message, $response->data);
                }
            } else {
                return $this->errorResponse("Methode not allow", null);
            }
        } catch (\Exception $e) {
            return $this->badResponse($e->getMessage(), null);
        }
    }

    public function postRequestOneId($app, $module, $feature, $title, $id, Request $request)
    {
        try {
            $result = $this->gatewayManagerRepository->proceedRequest($app, $module, $feature, $title);
            if ($result->data->methode == "POST") {
                $response = (object) $this->globalGatewayService->globalApiGatewayService($result->data, $request, "/" . $id);
                if ($response->success) {
                    return $this->successResponse($response->message, $response->data, isset($response->pagination) ? $response->pagination : null);
                } else {
                    return $this->errorResponse($response->message, $response->data);
                }
            } else {
                return $this->errorResponse("Methode not allow", null);
            }
        } catch (\Exception $e) {
            return $this->badResponse($e->getMessage(), null);
        }
    }

    public function putRequestOneId($app, $module, $feature, $title, $id, Request $request)
    {
        try {
            $result = $this->gatewayManagerRepository->proceedRequest($app, $module, $feature, $title);
            if ($result->data->methode == "PUT") {
                $response = (object) $this->globalGatewayService->globalApiGatewayService($result->data, $request, "/" . $id);
                if ($response->success) {
                    return $this->successResponse($response->message, $response->data, isset($response->pagination) ? $response->pagination : null);
                } else {
                    return $this->errorResponse($response->message, $response->data);
                }
            } else {
                return $this->errorResponse("Methode not allow", null);
            }
        } catch (\Exception $e) {
            return $this->badResponse($e->getMessage(), null);
        }
    }

    public function deleteRequestOneId($app, $module, $feature, $title, $id, Request $request)
    {
        try {
            $result = $this->gatewayManagerRepository->proceedRequest($app, $module, $feature, $title);
            if ($result->data->methode == "DELETE") {
                $response = (object) $this->globalGatewayService->globalApiGatewayService($result->data, $request, "/" . $id);
                if ($response->success) {
                    return $this->successResponse($response->message, $response->data, isset($response->pagination) ? $response->pagination : null);
                } else {
                    return $this->errorResponse($response->message, $response->data);
                }
            } else {
                return $this->errorResponse("Methode not allow", null);
            }
        } catch (\Exception $e) {
            return $this->badResponse($e->getMessage(), null);
        }
    }

    public function getRequestOneId($app, $module, $feature, $title, $id, Request $request)
    {
        try {
            $result = $this->gatewayManagerRepository->proceedRequest($app, $module, $feature, $title);
            if ($result->data->methode == "GET") {
                $response = (object) $this->globalGatewayService->globalApiGatewayService($result->data, $request, "/" . $id);
                if ($response->success) {
                    return $this->successResponse($response->message, $response->data, isset($response->pagination) ? $response->pagination : null);
                } else {
                    return $this->errorResponse($response->message, $response->data);
                }
            } else {
                return $this->errorResponse("Methode not allow", null);
            }
        } catch (\Exception $e) {
            return $this->badResponse($e->getMessage(), null);
        }
    }

    public function postRequestTwoId($app, $module, $feature, $title, $id, $idTwo, Request $request)
    {
        try {
            $result = $this->gatewayManagerRepository->proceedRequest($app, $module, $feature, $title);
            if ($result->data->methode == "POST") {
                $response = (object) $this->globalGatewayService->globalApiGatewayService($result->data, $request, "/" . $id, "/" . $idTwo);
                if ($response->success) {
                    return $this->successResponse($response->message, $response->data, isset($response->pagination) ? $response->pagination : null);
                } else {
                    return $this->errorResponse($response->message, $response->data);
                }
            } else {
                return $this->errorResponse("Methode not allow", null);
            }
        } catch (\Exception $e) {
            return $this->badResponse($e->getMessage(), null);
        }
    }

    public function putRequestTwoId($app, $module, $feature, $title, $id, $idTwo, Request $request)
    {
        try {
            $result = $this->gatewayManagerRepository->proceedRequest($app, $module, $feature, $title);
            if ($result->data->methode == "PUT") {
                $response = (object) $this->globalGatewayService->globalApiGatewayService($result->data, $request, "/" . $id, "/" . $idTwo);
                if ($response->success) {
                    return $this->successResponse($response->message, $response->data, isset($response->pagination) ? $response->pagination : null);
                } else {
                    return $this->errorResponse($response->message, $response->data);
                }
            } else {
                return $this->errorResponse("Methode not allow", null);
            }
        } catch (\Exception $e) {
            return $this->badResponse($e->getMessage(), null);
        }
    }

    public function deleteRequestTwoId($app, $module, $feature, $title, $id, $idTwo, Request $request)
    {
        try {
            $result = $this->gatewayManagerRepository->proceedRequest($app, $module, $feature, $title);
            if ($result->data->methode == "DELETE") {
                $response = (object) $this->globalGatewayService->globalApiGatewayService($result->data, $request, "/" . $id, "/" . $idTwo);
                if ($response->success) {
                    return $this->successResponse($response->message, $response->data, isset($response->pagination) ? $response->pagination : null);
                } else {
                    return $this->errorResponse($response->message, $response->data);
                }
            } else {
                return $this->errorResponse("Methode not allow", null);
            }
        } catch (\Exception $e) {
            return $this->badResponse($e->getMessage(), null);
        }
    }

    public function getRequestTwoId($app, $module, $feature, $title, $id, $idTwo, Request $request)
    {
        try {
            $result = $this->gatewayManagerRepository->proceedRequest($app, $module, $feature, $title);
            if ($result->data->methode == "GET") {
                $response = (object) $this->globalGatewayService->globalApiGatewayService($result->data, $request, "/" . $id, "/" . $idTwo);
                if ($response->success) {
                    return $this->successResponse($response->message, $response->data, isset($response->pagination) ? $response->pagination : null);
                } else {
                    return $this->errorResponse($response->message, $response->data);
                }
            } else {
                return $this->errorResponse("Methode not allow", null);
            }
        } catch (\Exception $e) {
            return $this->badResponse($e->getMessage(), null);
        }
    }

    public function updateRequest($app, $module, $feature, $title, $action, Request $request)
    {
        try {
            $response = $this->gatewayManagerRepository->updateRequest($app, $module, $feature, $title, $action, $request);
            if ($response->success) {
                return $this->successResponse($response->message, $response->data, isset($response->pagination) ? $response->pagination : null);
            } else {
                return $this->errorResponse($response->message, $response->data);
            }
        } catch (\Exception $e) {
            return $this->badResponse($e->getMessage(), null);
        }
    }

    public function addRequest(Request $request)
    {
        try {
            $response = $this->gatewayManagerRepository->create($request);
            if ($response->success) {
                return $this->successResponse($response->message, $response->data, isset($response->pagination) ? $response->pagination : null);
            } else {
                return $this->errorResponse($response->message, $response->data);
            }
        } catch (\Exception $e) {
            return $this->badResponse($e->getMessage(), null);
        }
    }
}
