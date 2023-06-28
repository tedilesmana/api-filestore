<?php

namespace App\Http\Resources\Menu;

use Illuminate\Http\Resources\Json\JsonResource;

class RouteResource extends JsonResource
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
            'route_code' => $this->route_code,
            'name' => $this->name,
            'title' => $this->title,
            'sub_title' => $this->sub_title,
            'path' => $this->path,
            'icon_url' => $this->icon_url,
            'access_permissions' => $this->access_permissions,
            'menu' => MenuResource::collection(is_null($this->menus) || count($this->menus) == 0 ? [] : $this->menus),
        ];
    }
}
