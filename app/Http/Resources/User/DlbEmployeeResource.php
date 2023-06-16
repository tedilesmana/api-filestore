<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class DlbEmployeeResource extends JsonResource
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
            'source_dlb_employee_id' => $this->source_dlb_employee_id,
            'staff_id' => $this->staff_id,
            'is_active' => $this->is_active,
            'program_studi' => $this->program_studi,
            'initial' => $this->initial,
            'nidn' => $this->nidn,
        ];
    }
}
