<?php

namespace App\Http\Resources\ImageStore;

use Illuminate\Http\Resources\Json\JsonResource;

class ImageStoreResource extends JsonResource
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
            'permission_code' => $this->permission_code,
            'description' => $this->description,
        ];
    }
}
