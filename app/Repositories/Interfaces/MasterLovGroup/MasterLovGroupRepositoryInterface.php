<?php

namespace App\Repositories\Interfaces\MasterLovGroup;

interface MasterLovGroupRepositoryInterface
{
    public function getAll($request);
    public function getById($id);
    public function create($request);
    public function update($request, $id);
    public function delete($id);
}
