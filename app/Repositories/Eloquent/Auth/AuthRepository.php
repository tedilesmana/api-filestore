<?php

namespace App\Repositories\Eloquent\Auth;

use App\Models\User;
use App\Http\Controllers\BaseController;
use App\Http\Resources\User\UserResource;
use App\Models\Lecturer;
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

        $lecture = [];
        $lecture['user_id'] = $user->id;
        $lecture['lecturer_id'] = $request["personal_id"];
        $lecture['periode_id'] = $request["periode_id"];
        $lecture['is_active'] = $request["is_active"];
        $lecture['level'] = $request["level"];
        $lecture['departement_id'] = $request["departement"];

        UserDetail::updateOrCreate([
            'user_id'   => $user->id,
        ], $lecture);

        $user_detail = [];
        $user_detail['user_id'] = $user->id;
        $user_detail['lecturer_id'] = $request["personal_id"];
        $user_detail['periode_id'] = $request["periode_id"];
        $user_detail['is_active'] = $request["is_active"];
        $user_detail['level'] = $request["level"];
        $user_detail['departement_id'] = $request["departement"];

        Lecturer::updateOrCreate([
            'user_id'   => $user->id,
        ], $user_detail);

        return $user;
    }

    public function login($request)
    {
        $username = $request->username;
        $password = $request->password;
        $email = $username . '@paramadina.ac.id';

        $sql_users = "SELECT A.id, A.email, A.phone, A.mobile_phone1, A.mobile_phone2, A.nama, A.inisial, A.tgl_lahir, A.isActive, B.track_jabatan_struktural as id_jabatan,
							B.track_department as id_depart
							FROM tbl_master_personal A
							LEFT JOIN hrd_personal_track_jbtn_struktural B ON A.personal_uid = B.refkey AND B.isActive = 1
							LEFT JOIN tbl_master_jabatan C ON B.track_jabatan_struktural = C.acajbt_uid
							WHERE A.email = '$email' AND A.isActive = 1 AND A.erased = 0 order by id_jabatan ;";
        $sql_periode = 'SELECT * FROM tbl_periode_cuti WHERE isActive = 1';

        $users = DB::select($sql_users)[0];
        $periode = DB::select($sql_periode)[0];

        $id = $users->id;

        $sql_user_access = "SELECT * from tbl_user_pass where user = $id limit 1";
        $user_access = DB::select($sql_user_access);

        $first_time_login = count($user_access) == 0;

        if ($first_time_login) {
            if ($password == $users->tgl_lahir) {
                $tbl_user_auth = User::where("email", $users->email)->get();

                if ($tbl_user_auth->count() == 0) {
                    $check_phone1 = empty($users->mobile_phone1) || is_null($users->mobile_phone1);
                    $check_phone2 = empty($users->mobile_phone2) || is_null($users->mobile_phone2);
                    $check_phone3 = empty($users->phone) || is_null($users->phone);
                    $phone1 = $check_phone2 ? $check_phone3 : $check_phone2;
                    $phone2 = $check_phone1 ? $phone1 : $check_phone1;

                    if (empty($phone2) || is_null($phone2)) {
                        return $this->apiController->falseResult("Nomor handphone kamu belum terdaftar", null);
                    } else {
                        $user_create = $this->register([
                            "name" => $users->nama,
                            "device_id" => $request->device_id,
                            "google_id" => $request->google_id,
                            "email" => $users->email,
                            "email_verified_at" => Carbon::now(),
                            "password" => $password,
                            "phone_number" => $phone2,
                            "personal_id" => $id,
                            "periode_id" => $periode->id_periode,
                            "is_active" => $users->isActive,
                            "level" => $users->id_jabatan,
                            "departement" => $users->id_depart,
                        ]);
                        if ($user_create->device_id == $request->device_id) {
                            $data = ["auth" => $this->createToken($user_create->username, $password), "user" => new UserResource($user_create)];
                            return $this->apiController->trueResult("Selamat datang kembali", $data);
                        } else if ($request->device_id == "Website") {
                            $data = ["auth" => $this->createToken($user_create->username, $password), "user" => new UserResource($user_create)];
                            return $this->apiController->trueResult("Selamat datang kembali", $data);
                        } else {
                            return $this->apiController->falseResult("Mohon masuk melalui device yang terdaftar", null);
                        }
                    }
                } else {
                    if ($tbl_user_auth->first()->device_id == "Website") {
                        if ($tbl_user_auth->first()->device_id == $request->device_id) {
                            $data = ["auth" => $this->createToken($tbl_user_auth->first()->username, $password), "user" => new UserResource($tbl_user_auth->first())];
                            return $this->apiController->trueResult("Selamat datang kembali", $data);
                        } else if ($request->device_id == "Website") {
                            $data = ["auth" => $this->createToken($tbl_user_auth->first()->username, $password), "user" => new UserResource($tbl_user_auth->first())];
                            return $this->apiController->trueResult("Selamat datang kembali", $data);
                        } else {
                            return $this->apiController->falseResult("Mohon masuk melalui device yang terdaftar", null);
                        }
                    }

                    if ($tbl_user_auth->first()->device_id == $request->device_id) {
                        $data = ["auth" => $this->createToken($tbl_user_auth->first()->username, $password), "user" => new UserResource($tbl_user_auth->first())];
                        return $this->apiController->trueResult("Selamat datang kembali", $data);
                    } else if ($request->device_id == "Website") {
                        $data = ["auth" => $this->createToken($tbl_user_auth->first()->username, $password), "user" => new UserResource($tbl_user_auth->first())];
                        return $this->apiController->trueResult("Selamat datang kembali", $data);
                    } else {
                        return $this->apiController->falseResult("Mohon masuk melalui device yang terdaftar", null);
                    }
                }
            } else {
                $tbl_user_auth = User::where("email", $users->email)->get();

                if ($tbl_user_auth->count() > 0) {
                    if ($tbl_user_auth->first()->device_id == "Website") {
                        if ($tbl_user_auth->first()->device_id == $request->device_id) {
                            $data = ["auth" => $this->createToken($tbl_user_auth->first()->username, $password), "user" => new UserResource($tbl_user_auth->first())];
                            return $this->apiController->trueResult("Selamat datang kembali", $data);
                        } else if ($request->device_id == "Website") {
                            $data = ["auth" => $this->createToken($tbl_user_auth->first()->username, $password), "user" => new UserResource($tbl_user_auth->first())];
                            return $this->apiController->trueResult("Selamat datang kembali", $data);
                        } else {
                            return $this->apiController->falseResult("Mohon masuk melalui device yang terdaftar", null);
                        }
                    }

                    if ($tbl_user_auth->first()->device_id == $request->device_id) {
                        $data = ["auth" => $this->createToken($tbl_user_auth->first()->username, $password), "user" => new UserResource($tbl_user_auth->first())];
                        return $this->apiController->trueResult("Selamat datang kembali", $data);
                    } else {
                        return $this->apiController->falseResult("Mohon masuk melalui device yang terdaftar", null);
                    }
                } else {
                    $check_phone1 = empty($users->mobile_phone1) || is_null($users->mobile_phone1);
                    $phone1 = !$check_phone1 ? $users->mobile_phone1 : $users->mobile_phone2;
                    $check_phone2 = empty($phone1) || is_null($phone1);
                    $phone2 = !$check_phone2 ? $phone1 : $users->phone;
                    $check_phone3 = empty($phone2) || is_null($phone2);

                    if ($check_phone3) {
                        return $this->apiController->falseResult("Nomor handphone kamu belum terdaftar", null);
                    } else {
                        $user_create = $this->register([
                            "name" => $users->nama,
                            "device_id" => $request->device_id,
                            "google_id" => $request->google_id,
                            "email" => $users->email,
                            "email_verified_at" => Carbon::now(),
                            "password" => $request->google_id,
                            "phone_number" => $phone2,
                            "personal_id" => $id,
                            "periode_id" => $periode->id_periode,
                            "is_active" => $users->isActive,
                            "level" => $users->id_jabatan,
                            "departement" => $users->id_depart,
                        ]);
                        if ($user_create->device_id == $request->device_id) {
                            $data = ["auth" => $this->createToken($user_create->username, $password), "user" => new UserResource($user_create)];
                            return $this->apiController->trueResult("Selamat datang kembali", $data);
                        } else if ($request->device_id == "Website") {
                            $data = ["auth" => $this->createToken($user_create->username, $password), "user" => new UserResource($user_create)];
                            return $this->apiController->trueResult("Selamat datang kembali", $data);
                        } else {
                            return $this->apiController->falseResult("Mohon masuk melalui device yang terdaftar", null);
                        }
                    }
                }
            }
        } else {
            if (md5($password) == $user_access[0]->password) {
                $tbl_user_auth = User::where("email", $users->email)->get();

                if ($tbl_user_auth->count() == 0) {
                    $check_phone1 = empty($users->mobile_phone1) || is_null($users->mobile_phone1);
                    $phone1 = !$check_phone1 ? $users->mobile_phone1 : $users->mobile_phone2;
                    $check_phone2 = empty($phone1) || is_null($phone1);
                    $phone2 = !$check_phone2 ? $phone1 : $users->phone;
                    $check_phone3 = empty($phone2) || is_null($phone2);

                    if ($check_phone3) {
                        return $this->apiController->falseResult("Nomor handphone kamu belum terdaftar", null);
                    } else {
                        $user_create = $this->register([
                            "name" => $users->nama,
                            "device_id" => $request->device_id,
                            "google_id" => $request->google_id,
                            "email" => $users->email,
                            "email_verified_at" => Carbon::now(),
                            "password" => $password,
                            "phone_number" => $phone2,
                            "personal_id" => $id,
                            "periode_id" => $periode->id_periode,
                            "is_active" => $users->isActive,
                            "level" => $users->id_jabatan,
                            "departement" => $users->id_depart,
                        ]);
                        if ($user_create->device_id == $request->device_id) {
                            $data = ["auth" => $this->createToken($user_create->username, $password), "user" => new UserResource($user_create)];
                            return $this->apiController->trueResult("Selamat datang kembali", $data);
                        } else if ($request->device_id == "Website") {
                            $data = ["auth" => $this->createToken($user_create->username, $password), "user" => new UserResource($user_create)];
                            return $this->apiController->trueResult("Selamat datang kembali", $data);
                        } else {
                            return $this->apiController->falseResult("Mohon masuk melalui device yang terdaftar", null);
                        }
                    }
                } else {
                    if ($tbl_user_auth->first()->device_id == "Website") {
                        $user_create = User::updateOrCreate([
                            'email'   => $request['email'],
                        ], [
                            "device_id" => $request->device_id,
                        ]);

                        if ($user_create->device_id == $request->device_id) {
                            $data = ["auth" => $this->createToken($user_create->username, $password), "user" => new UserResource($user_create)];
                            return $this->apiController->trueResult("Selamat datang kembali", $data);
                        } else if ($request->device_id == "Website") {
                            $data = ["auth" => $this->createToken($user_create->username, $password), "user" => new UserResource($user_create)];
                            return $this->apiController->trueResult("Selamat datang kembali", $data);
                        } else {
                            return $this->apiController->falseResult("Mohon masuk melalui device yang terdaftar", null);
                        }
                    }

                    if ($tbl_user_auth->first()->device_id == $request->device_id) {
                        $data = ["auth" => $this->createToken($tbl_user_auth->first()->username, $password), "user" => new UserResource($tbl_user_auth->first())];
                        return $this->apiController->trueResult("Selamat datang kembali", $data);
                    } else if ($request->device_id == "Website") {
                        $data = ["auth" => $this->createToken($tbl_user_auth->first()->username, $password), "user" => new UserResource($tbl_user_auth->first())];
                        return $this->apiController->trueResult("Selamat datang kembali", $data);
                    } else {
                        return $this->apiController->falseResult("Mohon masuk melalui device yang terdaftar", null);
                    }
                }
            } else {
                $tbl_user_auth = User::where("email", $users->email)->get();

                if ($tbl_user_auth->count() == 0) {
                    $check_phone1 = empty($users->mobile_phone1) || is_null($users->mobile_phone1);
                    $phone1 = !$check_phone1 ? $users->mobile_phone1 : $users->mobile_phone2;
                    $check_phone2 = empty($phone1) || is_null($phone1);
                    $phone2 = !$check_phone2 ? $phone1 : $users->phone;
                    $check_phone3 = empty($phone2) || is_null($phone2);

                    if ($check_phone3) {
                        return $this->apiController->falseResult("Nomor handphone kamu belum terdaftar", null);
                    } else {
                        $user_create = $this->register([
                            "name" => $users->nama,
                            "device_id" => $request->device_id,
                            "google_id" => $request->google_id,
                            "email" => $users->email,
                            "email_verified_at" => Carbon::now(),
                            "password" => $request->google_id,
                            "phone_number" => $phone2,
                            "personal_id" => $id,
                            "periode_id" => $periode->id_periode,
                            "is_active" => $users->isActive,
                            "level" => $users->id_jabatan,
                            "departement" => $users->id_depart,
                        ]);

                        if ($user_create->device_id == $request->device_id) {
                            $data = ["auth" => $this->createToken($user_create->username, $password), "user" => new UserResource($user_create)];
                            return $this->apiController->trueResult("Selamat datang kembali", $data);
                        } else if ($request->device_id == "Website") {
                            $data = ["auth" => $this->createToken($user_create->username, $password), "user" => new UserResource($user_create)];
                            return $this->apiController->trueResult("Selamat datang kembali", $data);
                        } else {
                            return $this->apiController->falseResult("Mohon masuk melalui device yang terdaftar", null);
                        }
                    }
                } else {
                    if ($request->device_id == "Website") {
                        if ($tbl_user_auth->first()->device_id == $request->device_id) {
                            $data = ["auth" => $this->createToken($tbl_user_auth->first()->username, $password), "user" => new UserResource($tbl_user_auth->first())];
                            return $this->apiController->trueResult("Selamat datang kembali", $data);
                        } else if ($request->device_id == "Website") {
                            $data = ["auth" => $this->createToken($tbl_user_auth->first()->username, $password), "user" => new UserResource($tbl_user_auth->first())];
                            return $this->apiController->trueResult("Selamat datang kembali", $data);
                        } else {
                            return $this->apiController->falseResult("Mohon masuk melalui device yang terdaftar", null);
                        }
                    }

                    if (Hash::check($request->password, $tbl_user_auth->first()->user_password) || Hash::check($request->password, $tbl_user_auth->first()->google_password)) {
                        if ($tbl_user_auth->first()->device_id == $request->device_id) {
                            $data = ["auth" => $this->createToken($tbl_user_auth->first()->username, $password), "user" => new UserResource($tbl_user_auth->first())];
                            return $this->apiController->trueResult("Selamat datang kembali", $data);
                        } else if ($request->device_id == "Website") {
                            $data = ["auth" => $this->createToken($tbl_user_auth->first()->username, $password), "user" => new UserResource($tbl_user_auth->first())];
                            return $this->apiController->trueResult("Selamat datang kembali", $data);
                        } else {
                            return $this->apiController->falseResult("Mohon masuk melalui device yang terdaftar", null);
                        }
                    } else {
                        return $this->apiController->falseResult("Password yang dimasukan salah", null);
                    }
                }
            }
        }
        return $this->apiController->falseResult("if kondisi tidak tervalidasi", null);
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
