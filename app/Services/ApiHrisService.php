<?php

namespace App\Services;

use App\Traits\ConsumesExternalService;

class ApiHrisService
{
    use ConsumesExternalService;

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

    //Presensi
    public function getAllPresensiByUserService()
    {
        return $this->performeRequest("GET", "/presensi?personal_id=503");
    }
}
