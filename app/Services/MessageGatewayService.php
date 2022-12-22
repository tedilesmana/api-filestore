<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class MessageGatewayService
{
    public function sendBySMS($time, $date, $content, $to)
    {
        $otpContent = 'Hati-hati penipuan! OTP ini tidak boleh di ketahui oleh orang lain, silahkan masukan nomor OTP berikut untuk menverifikasi nomor Handphone/WA yang kamu daftarkan ' . $content;

        $body_request = array(
            'time'          => $time,
            'date'          => $date,
            'premium'       => true,
            'content'       => $otpContent,
            'to'            => $to
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, env('MESSAGE_GATEWAY_BASE_API') . '/message/sms/send-text');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body_request));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'x-access-key: ' . env('ZUWINDA_API_KEY')
            )
        );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $request = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode == 200) {
            $result = json_decode($request, true);

            return $result;
        }
    }

    public function sendByWhatsApp($time, $date, $content, $to)
    {
        $phone = decodePhoneNumber($to);

        $otpContent = 'Hati-hati penipuan! OTP ini tidak boleh di ketahui oleh orang lain, silahkan masukan nomor OTP berikut untuk menverifikasi nomor Handphone/WA yang kamu daftarkan ' . $content;

        $body_request = array(
            'time'          => $time,
            'date'          => $date,
            'instances_id'  => env('ZUWINDA_WHATSAPP_INSTANCES_ID'),
            'content'       => $otpContent,
            'to'            => $phone
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, env('MESSAGE_GATEWAY_BASE_API') . '/message/whatsapp/send-text');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body_request));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'x-access-key: ' . env('ZUWINDA_API_KEY')
            )
        );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $request = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode == 200) {
            $result = json_decode($request, true);

            return $result;
        }
    }
}
