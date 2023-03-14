<?php

namespace App\Services\ApiHris;

use App\Traits\ConsumesExternalService;
use Illuminate\Support\Facades\Auth;

class PresensiService
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

    public function getAllPresensiByUserService()
    {
        return $this->performeRequest("GET", "/presensi?personal_id=" . Auth::user()->lecturer->lecturer_id);
    }

    public function getAllPresensiByDepartementService($id)
    {
        return $this->performeRequest("GET", "/presensi/" . $id . "?personal_id=" . Auth::user()->lecturer->lecturer_id . "&department_id=" . Auth::user()->lecturer->departement_id);
    }

    public function doPresensiCheckInCheckOutService($request)
    {

        $options = [
            'multipart' => [
                [
                    'name' => 'description',
                    'contents' => $request->description
                ],
                [
                    'name' => 'type',
                    'contents' => $request->type
                ],
                [
                    'name' => 'work_location',
                    'contents' => $request->work_location
                ],
                [
                    'name' => 'personal_id',
                    'contents' => Auth::user()->lecturer->lecturer_id
                ],
                [
                    'name' => 'departement',
                    'contents' => Auth::user()->lecturer->departement_id
                ],
                [
                    'name' => 'in_longitude',
                    'contents' => $request->in_longitude
                ],
                [
                    'name' => 'in_latitude',
                    'contents' => $request->in_latitude
                ],
                [
                    'name' => 'out_longitude',
                    'contents' => $request->out_longitude
                ],
                [
                    'name' => 'out_latitude',
                    'contents' => $request->out_latitude
                ]
            ]
        ];

        return $this->performeRequest("POST", "/presensi", $options);
    }


    // Master Location
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