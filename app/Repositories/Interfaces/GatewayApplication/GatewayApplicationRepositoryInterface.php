<?php

namespace App\Repositories\Interfaces\GatewayApplication;

interface GatewayApplicationRepositoryInterface
{
    public function getAll();
    public function getById($id);
    public function create($request);
    public function update($request, $id);
    public function delete($id);
}
