<?php

namespace App\Services;

use App\Traits\GlobalExternalService;

class GlobalApiGatewayService
{
    use GlobalExternalService;

    public function globalApiGatewayService($request)
    {
        return $this->performeRequest($request->methode, $request->link_api_application, $request->token);
    }
}
