<?php

namespace App\Services\ApiHris;

use App\Traits\ConsumesExternalService;

class WorkingShiftService
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

    public function getAllMasterLocationService()
    {
        return $this->performeRequest("GET", "/master-location");
    }

    public function getDetailMasterLocationService($id, $request)
    {
        return $this->performeRequest("GET", "/master-location/$id?latitude=$request->latitude&longitude=$request->longitude");
    }

    public function deleteMasterLocationService($id)
    {
        return $this->performeRequest("DELETE", "/master-location/" . $id);
    }

    public function createMasterLocationService($request)
    {
        $options = [
            'multipart' => [
                [
                    'name' => 'full_address',
                    'contents' => $request->full_address
                ],
                [
                    'name' => 'latitude',
                    'contents' => $request->latitude
                ],
                [
                    'name' => 'longitude',
                    'contents' => $request->longitude
                ]
            ]
        ];

        return $this->performeRequest("POST", "/master-location", $options);
    }

    public function updateMasterLocationService($request, $id)
    {
        $options = [
            'multipart' => [
                [
                    'name' => 'full_address',
                    'contents' => $request->full_address
                ],
                [
                    'name' => 'latitude',
                    'contents' => $request->latitude
                ],
                [
                    'name' => 'longitude',
                    'contents' => $request->longitude
                ]
            ]
        ];

        return $this->performeRequest("PUT", "/master-location/" . $id, $options);
    }
}
