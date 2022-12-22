<?php

namespace App\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

trait ComsumesExternalService
{
    /**
     * Send a request to any service
     * @return string
     */

    public function performeRequest($method, $requestUrl, $form_params = [], $headers = [])
    {
        $client = new Client();
        $headers = [
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI4IiwianRpIjoiNjVlYTE3MDU0NzUxZDVkYWMxYjA5Y2QyMmFkYzkwMDhjZTA3ZWM3MDZkNzk1NGNjYTE4ZTRlOWMyODY5Y2RiNmM2MTcwNzRmYzBjZWFmMDMiLCJpYXQiOjE2NzA4MzU0NDkuOTE3NDM5LCJuYmYiOjE2NzA4MzU0NDkuOTE3NDQyLCJleHAiOjE2NzIxMzE0NDkuOTA3MjYzLCJzdWIiOiIxNyIsInNjb3BlcyI6W119.Y69T2Kl9TCAr7rbrNDJJ_ql7Yu66IGXSCFuEO_D3Pst-Ud0uLSRa045NNIBbKr7FSX1BtWvQfy_-9Xy-0l-PSikjgyIwqlkhqhZMuJJE3zxNFpXJAx52tGUbIP5HVQtWsJbKvJsJfd2Z7rRp2-iNqReqQRW8aInPEYsev2Sp-iELG7rKgbzyZut36KBWgv330sxx0Sri9gR4O1465iY40nVWN8_XTTSab_6SAY2NZOp0Jf7RW_y2gR-LM2d19Wz4xSsvjV8B0LTDQfIIcaIug-IjrxoJET2rRgGWRXCEDijGS9OKfmzZxH_4mZFdlQ7jIIw-fX8l8GmhFqI-dI1E8uTgHscIOq8ITSAqN8DH3evH-9aJ9VfhIDAqeXtEkXey1QcDQZHvWvMxy3vgi0tQQfdzISPJWinZoCSqQHT4n7ARuKTxiEzoP6zNsMDbOMDru9E7TyDR937i3yXeClArZJ0uiV-fVdBnxDJiBlZq39FcTjRMWGsgy3XRkpZZz4lT6XqHkg9EXJErrMjFcizW_sma-SkGXthTBBsNbUlO3gFR2BnVfBsO12PvHUvI3xViwNHIl3NRBHgDxRD0_xdSKqYD-IeaWOsGE0KBEirkYI45hqFWGWlT0KMvXjg9GwOm1QMnoHccfCAJXXFKoDIAeGzaFVm0hX2UUHHxfWu4CNc'
        ];
        $request = new Request('GET', 'https://apidev-hris.paramadina.ac.id/api/presensi', $headers);
        $response = $client->sendAsync($request)->wait();
        return json_decode($response->getBody()->getContents());
    }
}
