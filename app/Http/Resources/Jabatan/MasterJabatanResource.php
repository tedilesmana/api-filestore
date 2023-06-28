<?php

namespace App\Http\Resources\Jabatan;

use Illuminate\Http\Resources\Json\JsonResource;

class MasterJabatanResource extends JsonResource
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
            'acajbt_uid' => $this->acajbt_uid,
            'description' => $this->description,
        ];
    }
}
