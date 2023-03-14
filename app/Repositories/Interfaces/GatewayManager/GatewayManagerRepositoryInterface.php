<?php

namespace App\Repositories\Interfaces\GatewayManager;

interface GatewayManagerRepositoryInterface
{
    public function create($request);
    public function proceedRequest($app, $module, $feature, $title);
    public function updateRequest($app, $module, $feature, $title, $action, $request);
}
