<?php

namespace App\Repositories\Interfaces\Role;

interface RoleRepositoryInterface
{
    public function addRoleUser($request);
    public function deleteRoleUser($request);
    public function getAll($request);
    public function getById($id);
    public function create($request);
    public function update($request, $id);
    public function delete($id);
}
