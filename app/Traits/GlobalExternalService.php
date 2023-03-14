<?php

namespace App\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

trait GlobalExternalService
{
    /**
     * Send a request to any service
     * @return string
     */

    public function performeRequest($method, $requestUrl, $token, $form_params = [], $headers = [])
    {
        $client = new Client();

        if (isset($token)) {
            $headers["Authorization"] = $token;
        }

        $request = new Request($method, $requestUrl, $headers);
        $response = $client->sendAsync($request,  $form_params)->wait();
        return json_decode($response->getBody()->getContents());
    }
}
