<?php

namespace App\Repositories\Eloquent\Auth;

use App\Models\User;
use App\Http\Controllers\BaseController;
use App\Http\Resources\User\UserResource;
use App\Models\Employee;
use App\Models\UserDetail;
use App\Repositories\Interfaces\Auth\AuthRepositoryInterface;
use App\Services\MessageGatewayService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
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
        $username = $this->randomUsername($request["name"]);
        $input = [];

        $input['user_password'] = Hash::make($request['password']);
        $input['name'] = $request["name"];
        $input['username'] = $username;
        $input['phone_number'] = $request["phone_number"];
        $input['email_verified_at'] = $request["email_verified_at"];
        $input['email'] = $request["email"];
        $input['google_password'] = Hash::make("Password@Paramadina");
        $input['google_id'] = $request["google_id"];
        $input['device_id'] = $request["device_id"];

        $user = User::updateOrCreate([
            'email'   => $request['email'],
        ], $input);

        $user_detail = [];
        $user_detail['user_id'] = $user->id;

        UserDetail::updateOrCreate([
            'user_id'   => $user->id,
        ], $user_detail);

        $latestDataEmployee = Employee::orderBy('id', 'desc')->latest()->first();
        $codeEmployee = generateCode($latestDataEmployee !== null ? $latestDataEmployee->employee_code : null, Employee::CODE);

        $lecture = [];
        $lecture['user_id'] = $user->id;
        $lecture['employee_code'] = $codeEmployee;
        $lecture['entry_year'] = null;
        $lecture['out_year'] = null;
        $lecture['inisial'] = null;
        $lecture['nidn'] = null;
        $lecture['source_employee_id'] = $request["personal_id"];
        $lecture['is_active'] = $request["is_active"];
        $lecture['level'] = $request["level"];
        $lecture['departement_id'] = $request["departement"];
        $lecture['direktorat_id'] = $request["direktorat"];

        Employee::updateOrCreate([
            'user_id'   => $user->id,
        ], $lecture);

        return $user;
    }

    public function login($request)
    {
        $password = $request->password;

        if (str_contains($request->email, 'paramadina.ac.id')) {
            $tbl_user_auth = User::where("email", $request->email)->first();

            if (strlen(is_null($tbl_user_auth->device_id) ? "" : $tbl_user_auth->device_id) == 0) {
                $input = [];
                $input['device_id'] = $request->device_id;

                $tbl_user_auth = User::updateOrCreate([
                    'email'   => $request['email'],
                ], $input);
            }

            if ($request->login_by == "google") {
                if ($tbl_user_auth->google_id) {
                    if ($request->device_id == $tbl_user_auth->device_id) {
                        $data = ["auth" => $this->createToken($tbl_user_auth->username, $password), "user" => new UserResource($tbl_user_auth)];
                        return $this->apiController->trueResult("Selamat datang kembali", $data);
                    } else if ($request->device_id == "Website") {
                        $data = ["auth" => $this->createToken($tbl_user_auth->username, $password), "user" => new UserResource($tbl_user_auth)];
                        return $this->apiController->trueResult("Selamat datang kembali", $data);
                    } else {
                        return $this->apiController->falseResult("Mohon masuk melalui device yang terdaftar", null);
                    }
                } else {
                    $input = [];
                    $input['google_password'] = Hash::make($password);
                    $input['google_id'] = $request->google_id;
                    $input['device_id'] = $request->device_id;

                    $tbl_user_auth = User::updateOrCreate([
                        'email'   => $request['email'],
                    ], $input);

                    if ($tbl_user_auth->device_id == $request->device_id) {
                        $data = ["auth" => $this->createToken($tbl_user_auth->username, $password), "user" => new UserResource($tbl_user_auth)];
                        return $this->apiController->trueResult("Selamat datang kembali", $data);
                    } else if ($request->device_id == "Website") {
                        $data = ["auth" => $this->createToken($tbl_user_auth->username, $password), "user" => new UserResource($tbl_user_auth)];
                        return $this->apiController->trueResult("Selamat datang kembali", $data);
                    } else {
                        return $this->apiController->falseResult("Mohon masuk melalui device yang terdaftar", null);
                    }
                }
            } else {
                if ($request->device_id == $tbl_user_auth->device_id) {
                    $data = ["auth" => $this->createToken($tbl_user_auth->username, $password), "user" => new UserResource($tbl_user_auth)];
                    return $this->apiController->trueResult("Selamat datang kembali", $data);
                } else if ($request->device_id == "Website") {
                    $data = ["auth" => $this->createToken($tbl_user_auth->username, $password), "user" => new UserResource($tbl_user_auth)];
                    return $this->apiController->trueResult("Selamat datang kembali", $data);
                } else {
                    return $this->apiController->falseResult("Mohon masuk melalui device yang terdaftar", null);
                }
            }
        } else {
            return $this->apiController->falseResult("silahkan login menggunakan email paramadina", null);
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

    public function createToken($username, $password)
    {
        $response = Http::post(config('services.oauth_server.uri'), [
            "client_secret" => config('services.oauth_server.client_secret'),
            "grant_type" => "password",
            "client_id" => config('services.oauth_server.client_id'),
            "username" => $username,
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
