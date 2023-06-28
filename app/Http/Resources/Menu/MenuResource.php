<?php

namespace App\Http\Resources\Menu;

use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
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
            'menu_code' => $this->menu_code,
            'name' => $this->name,
            'title' => $this->title,
            'sub_title' => $this->sub_title,
            'path' => $this->path,
            'icon_url' => $this->icon_url,
            'access_permissions' => $this->access_permissions,
            'sub_menu' => SubMenuResource::collection(is_null($this->subMenus) || count($this->subMenus) == 0 ? [] : $this->subMenus),
        ];
    }
}
