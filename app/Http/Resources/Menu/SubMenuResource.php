<?php

namespace App\Http\Resources\Menu;

use Illuminate\Http\Resources\Json\JsonResource;

class SubMenuResource extends JsonResource
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
            'sub_menu_code' => $this->sub_menu_code,
            'name' => $this->name,
            'title' => $this->title,
            'sub_title' => $this->sub_title,
            'path' => $this->path,
            'icon_url' => $this->icon_url,
            'access_permissions' => $this->access_permissions,
            'additional_menus' => AdditionalMenuResource::collection(is_null($this->additionalMenus) || count($this->additionalMenus) == 0 ? [] : $this->additionalMenus),
        ];
    }
}
