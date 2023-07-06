<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Jabatan\JabatanStrukturalResource;
use App\Http\Resources\Jabatan\RoleResource;
use App\Models\RoleUser;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        //File Upload Tes
        $havaStudent = !(is_null($this->student));
        $haveMhs =  $havaStudent ? !(is_null($this->student->mahasiswa)) : false;
        $id_mahasiswa = $havaStudent && $haveMhs ? $this->student->mahasiswa->id : null;
        $havaEmployee = !(is_null($this->employee));

        $role = [];

        if (str_contains($this->email, 'lecturer.paramadina.ac.id')) {
            $result = RoleUser::updateOrCreate([
                'user_id' => $this->id,
                'role_id' => 2
            ], [
                'user_id' => $this->id,
                'role_id' => 2
            ]);
            $role = [...$role, $result];
        }

        if ($this->student) {
            $result = RoleUser::updateOrCreate([
                'user_id' => $this->id,
                'role_id' => 1
            ], [
                'user_id' => $this->id,
                'role_id' => 1
            ]);
            $role = [...$role, $result];
        }

        $isActiveEmployee = false;
        $isActiveDlbEmployee = false;
        $isActiveStudent = false;

        if (!is_null($this->employee)) {
            if ($this->employee->is_active == 0) {
                $isActiveEmployee = false;
            } else {
                $isActiveEmployee = true;
            }
        }
        if (!is_null($this->dlbEmployee)) {
            if ($this->dlbEmployee->is_active == 0) {
                $isActiveDlbEmployee = false;
            } else {
                $isActiveDlbEmployee = true;
            }
        }
        if (!is_null($this->student)) {
            if (Carbon::now()->startOfDay()->gte($this->student->tanggal_lulus)) {
                $isActiveStudent = false;
            } else {
                $isActiveStudent = true;
            }
        }

        $roleJabatan = $havaEmployee && $isActiveEmployee ? JabatanStrukturalResource::collection($this->employee->trackJabatan) : [];
        $specialRole = $havaStudent && $isActiveStudent ? RoleResource::collection($this->roles) : [];

        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'phone_number' => $this->phone_number,
            'nik_ktp' => is_null($this->userDetail) ? null : $this->userDetail->nik_ktp,
            'employee' => $isActiveEmployee ? new EmployeeResource($this->employee) : null,
            'dlb_employee' => $isActiveDlbEmployee ? new DlbEmployeeResource($this->dlbEmployee) : null,
            'student' => $isActiveStudent ? new StudentResource($this->student) : null,
            'jabatan' => [...$specialRole, ...$roleJabatan],
            'id_mahasiswa' => $id_mahasiswa,
        ];
    }
}
