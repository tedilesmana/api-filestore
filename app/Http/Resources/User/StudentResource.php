<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'nim' => $this->nim,
            'tanggal_lulus' => $this->tanggal_lulus,
            'angkatan' => $this->angkatan,
            'semester_masuk' => $this->semester_masuk,
            'status' => $this->status,
            'nama' => is_null($this->mahasiswa) ? null : $this->mahasiswa->nama,
        ];
    }
}
