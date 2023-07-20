<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Jabatan\JabatanStrukturalResource;
use App\Http\Resources\Jabatan\RoleResource;
use App\Models\RoleUser;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleUserResource extends JsonResource
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
        $haveStudent = !(is_null($this->student));
        $haveMhs =  $haveStudent ? !(is_null($this->student->mahasiswa)) : false;
        $id_mahasiswa = $haveStudent && $haveMhs ? $this->student->mahasiswa->id : null;
        $haveEmployee = !(is_null($this->employee));
        $haveDlbEmployee = !(is_null($this->dlbEmployee));

        $role = [];

        if (str_contains($this->email, 'lecturer.paramadina.ac.id')) {
            $result = RoleUser::updateOrCreate([
                'user_id' => $this->id,
                'role_id' => 6
            ], [
                'user_id' => $this->id,
                'role_id' => 6
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

        $roleJabatan = $haveEmployee && $isActiveEmployee ? JabatanStrukturalResource::collection($this->employee->trackJabatan) : [];
        $specialRole = RoleResource::collection($this->roles);

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
