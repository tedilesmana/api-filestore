<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
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
            'source_employee_id' => $this->source_employee_id,
            'departement_id' => $this->departement_id,
            'direktorat_id' => $this->direktorat_id,
            'rektorat_id' => $this->rektorat_id,
            'personal_uid' => $this->personal_uid,
            'jabatan_id' => $this->jabatan_id,
            'is_active' => $this->is_active,
            'initial' => $this->initial,
            'nidn' => $this->nidn,
        ];
    }
}
