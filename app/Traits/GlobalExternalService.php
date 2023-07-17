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

    public function performeRequest($method, $requestUrl, $token, $body = [], $headers = [], $params = '')
    {
        $client = new Client();

        if (isset($token)) {
            $headers["Authorization"] = $token;
        }

        $request = new Request($method, $requestUrl . $params, $headers);
        $response = $client->sendAsync($request,  $body)->wait();
        if (str_contains($requestUrl, 'export')) {
            return $response;
        } else {
            return json_decode($response->getBody()->getContents());
        }
    }
}
