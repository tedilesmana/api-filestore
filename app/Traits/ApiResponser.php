<?php

namespace App\Traits;

use Symfony\Component\HttpFoundation\Response;

trait ApiResponser
{
    public function successResponse($message, $data)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], Response::HTTP_OK)->header('Content-Type', 'application/json');
    }

    public function unauthorizedResponse()
    {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized',
            'data' => null,
        ], Response::HTTP_UNAUTHORIZED);
    }

    public function errorResponse($message, $data)
    {
        if ($message == "The MAC is invalid.") {
            return response()->json([
                'success' => false,
                'message' => "Encrypt id tidak sesuai dengan key yang tersedia",
                'data' => $data,
            ], Response::HTTP_BAD_REQUEST);
        } else {
            return response()->json([
                'success' => false,
                'message' => $message,
                'data' => $data,
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function notLoginResponse()
    {
        return response()->json([
            'success' => false,
            'message' => 'Silahkan login terlebih dahulu',
            'data' => null,
        ], Response::HTTP_UNAUTHORIZED);
    }

    public function timeOutResponse()
    {
        return response()->json([
            'success' => false,
            'message' => 'Permintaan anda terkena timeout, silahkan coba lagi beberapa saat lagi',
            'data' => null,
        ], Response::HTTP_REQUEST_TIMEOUT);
    }

    public function notAccessResponse()
    {
        return response()->json([
            'success' => false,
            'message' => 'Anda tidak mempunyai akses ke file atau direktory yang di minta',
            'data' => null,
        ], Response::HTTP_FORBIDDEN);
    }

    public function unProsesEntityResponse($message, $data)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data,
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function badResponse($data)
    {
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan pada syntax code program',
            'data' => $data,
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function serverDownResponse()
    {
        return response()->json([
            'success' => false,
            'message' => 'Mohon maaf server sedang sibuk atau sedang dalam maintanance',
            'data' => null,
        ], Response::HTTP_SERVICE_UNAVAILABLE);
    }

    public function inActiveUserResponse($type)
    {
        return response()->json([
            'success' => false,
            'message' => 'Anda belum melakukan aktivasi ' . $type . ', silakan lakukan aktivasi terlebih dahulu',
            'data' => null,
        ], Response::HTTP_FORBIDDEN);
    }

    public function trueResult($message, $data)
    {
        return (object)[
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];
    }

    public function falseResult($message, $data)
    {
        return (object)[
            'success' => false,
            'message' => $message,
            'data' => $data,
        ];
    }
}
