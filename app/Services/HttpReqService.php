<?php

namespace App\Services;

class HttpReqService
{
    public function makeReq(string $url, array $curlOpt) {
        $ch = curl_init($url);

        curl_setopt_array($ch, $curlOpt);

        $res = curl_exec($ch);

        if ($res === false) {
            curl_close($ch);

            return [
                'status' => 'fail',
                'msg' => 'cURL error: ' . curl_error($ch)
            ];
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if($httpCode != 200) {
            return [
                'status' => 'fail',
                'msg' => "HTTP request failed with code: $httpCode"
            ];
        }

        $data = json_decode($res, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "JSON error: " . json_last_error_msg();
            return [
                'status' => 'fail',
                'msg' => "JSON error: " . json_last_error_msg()
            ];
        }

        return [
            'status' => 'ok',
            'data' => $data
        ];
    }

}
