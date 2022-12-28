<?php

namespace App\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

trait ConsumesExternalService
{
    /**
     * Send a request to any service
     * @return string
     */

    public function performeRequest($method, $requestUrl, $form_params = [], $headers = [])
    {
        $client = new Client();

        if (isset($this->secret)) {
            $headers["Authorization"] = $this->secret;
        }

        $request = new Request($method, 'https://apidev-hris.paramadina.ac.id/api' . $requestUrl, $headers);
        $response = $client->sendAsync($request,  $form_params)->wait();
        return json_decode($response->getBody()->getContents());
    }
}
