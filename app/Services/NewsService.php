<?php

namespace App\Services;

use App\Services\HttpReqService;
use DateTime;

class NewsService
{
    private const API_KEY = 'YOUR_TOKEN_HERE';

    public function getNews(): array {
        $httpReq = new HttpReqService();

        $data = $this->makeHttpReq(self::API_KEY, $httpReq);

        //IF FAIL
        if($data['status'] === 'fail') return $data;

        //IF OK
        if(
            $data['status'] === 'ok'
            && isset($data['data']['articles'])
        ) {
            $articles = $this->jsonNewsNormalizer($data['data']['articles']);

            return [
                'status' => 'ok',
                'articles' => $articles
            ];
        }
    }

    private function makeHttpReq($apiKey, $req) {
        $curlOpt = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_ENCODING => "",
            CURLOPT_HTTPHEADER => [
                "Accept: application/json",
                "User-Agent: NewsAggregator/1.0",
            ],
        ];

        $searchStr = urlencode('(игры OR gaming OR videogames OR видеоигры)');

        $apiUrl = "https://gnews.io/api/v4/search?q=$searchStr&lang=ru&max=10&sortby=publishedAt&apikey=$apiKey";

        $result = $req->makeReq($apiUrl, $curlOpt);

        return $result;
    }

    private function jsonNewsNormalizer(array $articles) {
        if(!is_array($articles) || empty($articles)) return [];
        
        $articlesAr = [];
        foreach($articles as $a) {
            if(
                isset($a['id'])
                && isset($a['title'])
                && isset($a['content'])
                && isset($a['url'])
                && isset($a['publishedAt'])
            ) {
                $date = $this->dateNormalizer($a['publishedAt']);

                $text = $a['title'] . '. ' . $a['description'];
                $articlesAr[] = [
                    'api_id' => $a['id'],
                    'title' => $a['title'],
                    'text' => $text,
                    'source' => $a['url'],
                    'facts' => '',
                    'status' => 'no_mod',
                    'published_at' => $date
                ];
            }
        }

        return $articlesAr;
    }

    private function dateNormalizer($dateStr) {
        $date = new DateTime($dateStr);

        return $date->format('Y-m-d H:i:s');
    }
}
