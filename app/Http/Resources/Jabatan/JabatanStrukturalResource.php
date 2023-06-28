<?php

namespace App\Http\Resources\Jabatan;

use Illuminate\Http\Resources\Json\JsonResource;

class JabatanStrukturalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return new MasterJabatanResource($this->jabatan);
    }
}
