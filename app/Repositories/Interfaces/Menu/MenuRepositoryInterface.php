<?php

namespace App\Repositories\Interfaces\Menu;

interface MenuRepositoryInterface
{
    public function getAll($request);
    public function getById($id);
    public function create($request);
    public function update($request, $id);
    public function delete($id);
}
