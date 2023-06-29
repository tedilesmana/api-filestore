<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\BaseController;
use App\Repositories\Interfaces\Auth\AuthRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends BaseController
{
    protected $authRepository;
    public function __construct(AuthRepositoryInterface $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    /**
     * Display a listing of the resources.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $response = $this->authRepository->getAllUser($request);
            if ($response->success) {
                return $this->successResponse($response->message, $response->data->data, $response->data->pagination);
            } else {
                return $this->errorResponse($response->message, $response->data);
            }
        } catch (\Exception $e) {
            return $this->badResponse($e->getMessage(), $e);
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
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
    public function destroy($id)
    {
        //
    }

    public function unauthorize()
    {
        return $this->unauthorizedResponse();
    }

    public function login(Request $request)
    {
        try {
            $response = $this->authRepository->login($request);
            if ($response->success) {
                return $this->successResponse('Anda telah berhasil masuk', $response->data);
            } else {
                return $this->errorResponse($response->message, $response->data);
            }
        } catch (\Exception $e) {
            return $this->badResponse($e->getMessage(), $e);
        }
    }

    public function loginWithWhatsApp(Request $request)
    {
        try {
            $response = $this->authRepository->loginWithWhatsApp($request);
            if ($response->success) {
                return $this->successResponse($response->message, $response->data);
            } else {
                return $this->errorResponse($response->message, $response->data);
            }
        } catch (\Exception $e) {
            return $this->badResponse($e->getMessage(), null);
        }
    }

    public function generateTokenWhatsApp(Request $request)
    {
        try {
            $response = $this->authRepository->createTokenWithWhatsApp($request);
            if ($response->success) {
                return $this->successResponse($response->message, $response->data);
            } else {
                return $this->errorResponse($response->message, $response->data);
            }
        } catch (\Exception $e) {
            return $this->badResponse($e->getMessage(), null);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listJabatan(Request $request)
    {
        try {
            $response = $this->authRepository->listJabatan($request);
            if ($response->success) {
                return $this->successResponse($response->message, $response->data->data, $response->data->pagination);
            } else {
                return $this->errorResponse($response->message, $response->data);
            }
        } catch (\Exception $e) {
            return $this->badResponse($e->getMessage(), $e);
        }
    }

    public function logout()
    {
        try {
            if (Auth::check()) {
                Auth::user()->token()->revoke();
                return $this->successResponse('Sampai jumpa kembali...', 'kamu sudah berhasil keluar');
            } else {
                return response()->json(['error' => 'user token access not found'], 500);
            }
        } catch (\Exception $e) {
            return $this->badResponse($e->getMessage(), null);
        }
    }
}
