<?php

namespace App\Services;

use App\Traits\GlobalExternalService;

class GlobalApiGatewayService
{
    use GlobalExternalService;

    public function globalApiGatewayService($result, $request, $id = "", $idTwo = "")
    {
        $params = '';
        $multipart = [];
        foreach ($request->all() as $key => $value) {
            if (strlen($params) == 0) {
                $params = '?' . $key . '=' . $value ?? '';
            } else {
                $params = $params . '&' . $key . '=' . $value ?? '';
            }
            $multipart = [...$multipart, [
                'name' => $key,
                'contents' => $value
            ]];
        }

        return $this->performeRequest($result->methode, $result->link_api_application . $id . $idTwo, $result->token, [], [], $params);
    }
}
