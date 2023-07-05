<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Jabatan\JabatanStrukturalResource;
use App\Http\Resources\Jabatan\RoleResource;
use App\Models\RoleUser;
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

        $roleJabatan = $havaEmployee ? JabatanStrukturalResource::collection($this->employee->trackJabatan) : [];
        $specialRole = RoleResource::collection($this->roles);

        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'phone_number' => $this->phone_number,
            'nik_ktp' => is_null($this->userDetail) ? null : $this->userDetail->nik_ktp,
            'employee' => new EmployeeResource($this->employee),
            'dlb_employee' => new DlbEmployeeResource($this->dlbEmployee),
            'student' => new StudentResource($this->student),
            'jabatan' => [...$specialRole, ...$roleJabatan],
            'id_mahasiswa' => $id_mahasiswa,
        ];
    }
}
