<?php

namespace App\Http\Resources\Menu;

use Illuminate\Http\Resources\Json\JsonResource;

class AdditionalMenuResource extends JsonResource
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
            'additional_menu_code' => $this->additional_menu_code,
            'name' => $this->name,
            'title' => $this->title,
            'sub_title' => $this->sub_title,
            'path' => $this->path,
            'icon_url' => $this->icon_url,
            'access_permissions' => $this->access_permissions,
        ];
    }
}
