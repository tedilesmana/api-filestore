<?php

namespace App\Repositories\Eloquent\Auth;

use App\Models\User;
use App\Http\Controllers\BaseController;
use App\Repositories\Interfaces\Auth\AuthRepositoryInterface;
use App\Services\MessageGatewayService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class AuthRepository implements AuthRepositoryInterface
{
    protected $apiController;
    protected $messageGatewayService;

    public function __construct(BaseController $apiController, MessageGatewayService $messageGatewayService)
    {
        $this->apiController = $apiController;
        $this->messageGatewayService = $messageGatewayService;
    }

    function randomUsername($string)
    {
        $pattern = " ";
        $firstPart = strstr(strtolower($string), $pattern, true);
        $secondPart = substr(strstr(strtolower($string), $pattern, false), 0, 3);
        $nrRand = rand(0, 10000);

        $username = trim($firstPart) . trim($secondPart) . trim($nrRand);
        return $username;
    }

    public function register($request)
    {
        try {
            $input = [];
            $input['name'] = $request->name;
            $input['email'] = $request->email;
            $input['phone_number'] = $request->phone_number;
            $input['password'] = Hash::make($request->password);

            $user = User::updateOrCreate([
                'email'   => $request->email,
            ], $input);

            if ($user) {
                return $this->apiController->trueResult("Akun kamu telah berhasil terdaftar", $user);
            } else {
                return $this->apiController->falseResult("Proses registrasi gagal silahkan coba kembali", null);
            }
        } catch (\Throwable $th) {
            return $this->apiController->falseResult("Proses registrasi gagal silahkan coba kembali", $th);
        }
    }

    public function login($request)
    {
        $email = $request->email;
        $password = $request->password;

        $users = User::where("email", $email)->first();

        if (Hash::check($request->password, $users->password)) {
            if ($users->device_id == $request->device_id) {
                $data = ["auth" => $this->createToken($users->email, $password), "user" => $users];
                return $this->apiController->trueResult("Selamat datang kembali", $data);
            } else {
                return $this->apiController->falseResult("Mohon masuk melalui device yang terdaftar", null);
            }
        } else {
            return $this->apiController->falseResult("Password yang dimasukan salah", null);
        }
    }

    public function loginWithWhatsApp($request)
    {
        $user = User::where('phone_number', '=', $request->phone_number)->first();

        if ($user) {
            $user->otp = mt_rand(100000, 999999);
            $user->save();

            $responseMessageGateway = $this->messageGatewayService->sendByWhatsApp(Carbon::now()->format('H:i'), Carbon::now()->format('Y-m-d'), $user->otp, $user->phone_number);

            return $this->apiController->trueResult("Kode OTP telah di kirimkan ke nomor handphone yang terdaftar melalui WA", $responseMessageGateway);
        } else {
            return $this->apiController->falseResult("Nomor handphone yang kamu masukan tidak terdaftar", null);
        }
    }

    public function createToken($email, $password)
    {
        $response = Http::post(config('services.oauth_server.uri'), [
            "client_secret" => config('services.oauth_server.client_secret'),
            "grant_type" => "password",
            "client_id" => config('services.oauth_server.client_id'),
            "username" => $email,
            "password" => $password,
            'scope' => '',
        ]);

        if (!$response->ok()) throw new Exception('Error create auth clients');

        $token = json_decode((string)$response->body());

        return $token;
    }

    public function createTokenWithWhatsApp($request)
    {
        $response = Http::post(config('services.oauth_server.uri'), [
            "client_secret" => config('services.oauth_server.client_secret'),
            "grant_type" => "otp_grant",
            "client_id" => config('services.oauth_server.client_id'),
            "phone_number" => $request->phone_number,
            "otp" => $request->otp,
            "scope" => '',
        ]);

        if (!$response->ok()) throw new Exception('Error create auth clients');

        $user = User::where('otp', $request->otp)->first();
        $user->otp = null;
        $user->save();

        $token = json_decode((string)$response->body());

        return $token;
    }
}
