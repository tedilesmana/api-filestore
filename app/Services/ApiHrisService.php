<?php

namespace App\Services;

use App\Traits\ComsumesExternalService;

class ApiHrisService
{
    use ComsumesExternalService;

    /**
     * [$baseUri description]
     * @var [type]
     */
    public $baseUri;
    public $secret;

    public function __construct()
    {
        $this->baseUri = config('services.hris_api.base_uri');
        $this->secret = config('services.hris_api.secret');
    }

    public function getAllPresensiByUserService()
    {
        return $this->performeRequest("GET", "/presensi");
    }
}
