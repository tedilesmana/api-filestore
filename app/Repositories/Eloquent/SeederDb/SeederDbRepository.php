<?php

namespace App\Repositories\Eloquent\SeederDb;

use App\Http\Controllers\BaseController;
use App\Models\DlbEmployee;
use App\Models\DosenData;
use App\Models\Employee;
use App\Models\GsuiteMahasiswa;
use App\Models\Mahasiswa;
use App\Models\MasterPersonal;
use App\Models\Student;
use App\Models\User;
use App\Models\UserDetail;
use App\Repositories\Interfaces\SeederDb\SeederDbRepositoryInterface;
use App\Services\MessageGatewayService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class SeederDbRepository implements SeederDbRepositoryInterface
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

    function randomPhone()
    {
        $nrRand = rand(100000000000, 999999999999);

        $username = trim($nrRand);
        return $username;
    }

    public function insertMahasiswa($page)
    {
        $students  = Mahasiswa::select('mhsTanggalLahir', 'mhsTempatLahir', 'mhsJenisKelamin', 'mhsAlamatMhs', 'mhsKodePos', 'mhsNik', 'mhsNiu', 'mhsNif', 'mhsAngkatan', 'mhsStatus', 'mhsTanggalTerdaftar', 'mhsTanggalLulus', 'mhsSemesterMasuk', 'mhsNama', 'mhsNoHp')->offset(($page - 1) * 100)->limit(100)->get();

        foreach ($students as $key => $value) {
            $gsuite  = GsuiteMahasiswa::where('nim', $value->mhsNiu)->first();
            if ($gsuite) {
                if (!is_null($gsuite->gsuite)) {
                    if (strlen($gsuite->gsuite) > 0) {
                        $latestDataMhs = Student::orderBy('id', 'desc')->latest()->first();
                        $codeStudent = generateCode($latestDataMhs !== null ? $latestDataMhs->student_code : null, Student::CODE);

                        $username = $this->randomUsername($value->mhsNama);
                        $input = [];

                        $phone = strlen(is_null($value->mhsNoHp) ? "" : $value->mhsNoHp) > 0 ? $value->mhsNoHp : '-';

                        $input['user_password'] = Hash::make("Password@Paramadina");
                        $input['name'] = $value->mhsNama;
                        $input['username'] = $username;
                        $input['phone_number'] = is_null($value->mhsNoHp) || $value->mhsNoHp == '-' ? '-' : $phone;
                        $input['email_verified_at'] = Carbon::now();
                        $input['email'] = $gsuite->gsuite;
                        $input['google_password'] = Hash::make("Password@Paramadina");
                        $input['google_id'] = null;
                        $input['device_id'] = null;

                        $user = User::updateOrCreate([
                            'email'   => $input['email'],
                        ], $input);

                        $user_detail = [];
                        $user_detail['user_id'] = $user->id;
                        $user_detail['tanggal_lahir'] = $value->mhsTanggalLahir == '0000-00-00' ? null : $value->mhsTanggalLahir;
                        $user_detail['tempat_lahir'] = $value->mhsTempatLahir;
                        $user_detail['jenis_kelamin'] = $value->mhsJenisKelamin;
                        $user_detail['alamat'] = $value->mhsAlamatMhs;
                        $user_detail['agama'] = null;
                        $user_detail['kode_pos'] = $value->mhsKodePos;
                        $user_detail['nik_ktp'] = $value->mhsNik;

                        UserDetail::updateOrCreate([
                            'user_id'   => $user->id,
                        ], $user_detail);

                        $isExist = Student::where('user_id', $user->id)->get();
                        $student = [];
                        $student['user_id'] = $user->id;
                        if ($isExist->count() == 0) {
                            $student['student_code'] = $codeStudent;
                        } else {
                            $student['student_code'] = $isExist->first()->student_code;
                        }
                        $student['nim'] = $value->mhsNiu;
                        $student['nif'] = $value->mhsNif;
                        $student['angkatan'] = $value->mhsAngkatan;
                        $student['status'] = $value->mhsStatus;
                        $student['tanggal_terdaftar'] = $value->mhsTanggalTerdaftar == '0000-00-00' ? null : $value->mhsTanggalTerdaftar;
                        $student['tanggal_lulus'] = $value->mhsTanggalLulus;
                        $student['semester_masuk'] = $value->mhsSemesterMasuk;

                        Student::updateOrCreate([
                            'user_id'   => $user->id,
                        ], $student);
                    }
                }
            }
        }
        return $this->apiController->trueResult("Seeder Berhasil", $students->count());
    }

    public function insertEmployees($page)
    {
        $employees  = MasterPersonal::offset(($page - 1) * 100)->limit(100)->get();

        foreach ($employees as $key => $value) {
            if (!is_null($value->email)) {
                if (strlen($value->email) > 0) {
                    $latestDataEmployee = Employee::orderBy('id', 'desc')->latest()->first();
                    $codeEmployee = generateCode($latestDataEmployee !== null ? $latestDataEmployee->employee_code : null, Employee::CODE);

                    $username = $this->randomUsername($value->nama);
                    $input = [];

                    $phone = strlen(is_null($value->mobile_phone1) ? "" : $value->mobile_phone1) > 0 ? $value->mobile_phone1 : '-';
                    $input['user_password'] = Hash::make("Password@Paramadina");
                    $input['name'] = $value->nama;
                    $input['username'] = $username;
                    $input['phone_number'] = is_null($value->mobile_phone1) || $value->mobile_phone1 == '-' ? '-' : $phone;
                    $input['email_verified_at'] = Carbon::now();
                    $input['email'] = $value->email;
                    $input['google_password'] = Hash::make("Password@Paramadina");
                    $input['google_id'] = null;
                    $input['device_id'] = null;

                    $user = User::updateOrCreate([
                        'email'   => $input['email'],
                    ], $input);

                    $user_detail = [];
                    $user_detail['user_id'] = $user->id;
                    $user_detail['tanggal_lahir'] = $value->tgl_lahir == '0000-00-00' ? null : $value->tgl_lahir;
                    $user_detail['tempat_lahir'] = $value->tmpt_lahir;
                    $user_detail['jenis_kelamin'] = null;
                    $user_detail['alamat'] = $value->alamat_skr;
                    $user_detail['agama'] = null;
                    $user_detail['kode_pos'] = $value->kodepos_skr;
                    $user_detail['nik_ktp'] = $value->KTP;

                    UserDetail::updateOrCreate([
                        'user_id'   => $user->id,
                    ], $user_detail);

                    $isExist = Employee::where('user_id', $user->id)->get();
                    $lecture = [];
                    $lecture['user_id'] = $user->id;
                    if ($isExist->count() == 0) {
                        $lecture['employee_code'] = $codeEmployee;
                    } else {
                        $lecture['employee_code'] = $isExist->first()->employee_code;
                    }
                    $lecture['entry_year'] = $value->tgl_bergabung == '0000-00-00' ? null : $value->tgl_bergabung;
                    $lecture['out_year'] = $value->tgl_nonActive == '0000-00-00' ? null : $value->tgl_nonActive;
                    $lecture['inisial'] = $value->inisial;
                    $lecture['nidn'] = $value->NIDN;
                    $lecture['source_employee_id'] = $value->id;
                    $lecture['is_active'] = $value->isActive == 1 && $value->erased == 0 ? 1 : 0;
                    $lecture['jabatan_id'] = $value->id_jabatan;
                    $lecture['departement_id'] = $value->id_depart;
                    $lecture['direktorat_id'] = $value->id_direktorat;
                    $lecture['rektorat_id'] = $value->id_rektorat;
                    $lecture['personal_uid'] = $value->personal_uid;

                    Employee::updateOrCreate([
                        'user_id'   => $user->id,
                    ], $lecture);
                }
            }
        }
        return $this->apiController->trueResult("Seeder Berhasil", $employees->count());
    }

    public function insertDlbEmployees($page)
    {
        $employees  = DosenData::offset(($page - 1) * 100)->limit(100)->get();

        foreach ($employees as $key => $value) {
            if (!is_null($value->email_paramadina)) {
                if (strlen($value->email_paramadina) > 0) {
                    $latestDataDlbEmployee = DlbEmployee::orderBy('id', 'desc')->latest()->first();
                    $codeDlbEmployee = generateCode($latestDataDlbEmployee !== null ? $latestDataDlbEmployee->dlb_employee_code : null, DlbEmployee::CODE);

                    $username = $this->randomUsername($value->nama);
                    $input = [];

                    $phone = strlen(is_null($value->tlp_hp) ? "" : $value->tlp_hp) > 0 ? $value->tlp_hp : '-';
                    $input['user_password'] = Hash::make("Password@Paramadina");
                    $input['name'] = $value->nama;
                    $input['username'] = $username;
                    $input['phone_number'] = is_null($value->tlp_hp) || $value->tlp_hp == '-' ? '-' : $phone;
                    $input['email_verified_at'] = Carbon::now();
                    $input['email'] = $value->email_paramadina;
                    $input['google_password'] = Hash::make("Password@Paramadina");
                    $input['google_id'] = null;
                    $input['device_id'] = null;

                    $user = User::updateOrCreate([
                        'email'   => $input['email'],
                    ], $input);

                    $user_detail = [];
                    $user_detail['user_id'] = $user->id;
                    $user_detail['tanggal_lahir'] = $value->tgl_lahir == '0000-00-00' ? null : $value->tgl_lahir;
                    $user_detail['tempat_lahir'] = $value->kota_lahir;
                    $user_detail['jenis_kelamin'] = null;
                    $user_detail['alamat'] = $value->alamat;
                    $user_detail['agama'] = null;
                    $user_detail['kode_pos'] = $value->kodepos;
                    $user_detail['nik_ktp'] = $value->no_nik;

                    UserDetail::updateOrCreate([
                        'user_id'   => $user->id,
                    ], $user_detail);

                    $isExist = DlbEmployee::where('user_id', $user->id)->get();
                    $lecture = [];
                    $lecture['user_id'] = $user->id;
                    if ($isExist->count() == 0) {
                        $lecture['dlb_employee_code'] = $codeDlbEmployee;
                    } else {
                        $lecture['dlb_employee_code'] = $isExist->first()->dlb_employee_code;
                    }
                    $lecture['source_dlb_employee_id'] = $value->uid;
                    $lecture['program_studi'] = $value->program_studi;
                    $lecture['initial'] = $value->inisial;
                    $lecture['nidn'] = $value->nidn;
                    $lecture['staff_id'] = $value->staff_id;
                    $lecture['is_active'] = $value->isActive;

                    DlbEmployee::updateOrCreate([
                        'user_id'   => $user->id,
                    ], $lecture);
                }
            }
        }
        return $this->apiController->trueResult("Seeder Berhasil", $employees->count());
    }
}
