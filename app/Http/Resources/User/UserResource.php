<?php

namespace App\Http\Resources\User;

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

        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'phone_number' => $this->phone_number,
            'employee' => $this->employee,
            'dlb_employee' => $this->dlbEmployee,
            'student' => $this->student,
            'id_mahasiswa' => $id_mahasiswa
        ];
    }
}
