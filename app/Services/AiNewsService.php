<?php

namespace App\Services;
use App\Services\HttpReqService;

class AiNewsService
{
    private const API_KEY = 'YOUR_TOKEN_HERE';

    public function getMoodNews(array $articles): array {
        $httpReq = new HttpReqService();

        $data = $this->makeHttpReq(self::API_KEY, $httpReq, $articles);

        return $data;
    }

    private function makeHttpReq($apiKey, $req, $articles) {
        $apiUrl = 'https://generativelanguage.googleapis.com/v1/interactions';

        $modArticles = [];

        foreach($articles as $a) {
            $prompt = $this->getReqPrompt($a['text']);

            $curlOpt = [
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,

                CURLOPT_HTTPHEADER => [
                    'x-goog-api-key: ' . $apiKey,
                    'Content-Type: application/json',
                ],

                CURLOPT_POSTFIELDS => json_encode($prompt),
            ];

            $result = $req->makeReq($apiUrl, $curlOpt);

            if(isset($result['data']['steps'])) {
                foreach($result['data']['steps'] as $step) {
                    if(isset($step['content'][0]['text'])) {
                        //check result and make array
                        $checkResultAr = $this->checkReqResult($a['id'], json_decode($step['content'][0]['text'], true));

                        $modArticles[] = $checkResultAr;
                    }
                }
            } else {
                $modArticles[] = [
                    'status' => 'fail',
                    'aId' => $a['id'],
                    'msg' => 'Json not exist in AI result'
                ];
            }
        }

        return $modArticles;
    }

    private function checkReqResult($aId, $result): array {
        if (
            !isset($result['original_facts']) ||
            !isset($result['emotions']['positive']['text']) ||
            !isset($result['emotions']['positive']['facts']) ||
            !isset($result['emotions']['negative']['text']) ||
            !isset($result['emotions']['negative']['facts']) ||
            !isset($result['emotions']['neutral']['text']) ||
            !isset($result['emotions']['neutral']['facts']) ||
            !isset($result['emotions']['ironic']['text']) ||
            !isset($result['emotions']['ironic']['facts'])
        ) {
            return [
                'status' => 'fail',
                'aId' => $aId,
                'msg' => 'Some emotions or fields are missing'
            ];
        }

        if (!$this->areFactsSame($result)) {
            return [
                'status' => 'fail',
                'aId' => $aId,
                'msg' => 'Facts not same'
            ];
        }

        return [
            'status' => 'ok',
            'aId' => $aId,
            'data' => $result
        ];
    }

    private function areFactsSame($data): bool {
        foreach (['positive', 'negative', 'neutral', 'ironic'] as $mood) {
            if (!$this->factsSimilarEnough($data['original_facts'], $data['emotions'][$mood]['facts'])) {
                return false;
            }
        }

        return true;
    }

    private function factsSimilarEnough(array $originalFacts, array $modifiedFacts, float $threshold = 50.0): bool {
        $normalize = fn (string $s) => trim(preg_replace(
            '/[^\p{L}\p{N}\s]/u',
            '',
            mb_strtolower($s)
        ));

        $orig = implode(' ', array_map($normalize, $originalFacts));
        $mod = implode(' ', array_map($normalize, $modifiedFacts));

        similar_text($orig, $mod, $percent);

        return $percent >= $threshold;
    }

    private function getReqPrompt($article) {
        return [
            'model' => 'gemini-3.5-flash-lite',
            'input' => "Верни ответ строго в формате JSON.

                        Не добавляй markdown, ```json или какой-либо другой текст до или после JSON.

                        Твоя задача — переписать исходную новость в нескольких эмоциональных стилях.

                        КРИТИЧЕСКИ ВАЖНО:

                        Эмоциональный окрас должен изменять ТОЛЬКО стиль и тон повествования.
                        Фактическое содержание новости изменять запрещено.

                        ЗАПРЕЩЕНО:
                        - добавлять факты, которых нет в исходной новости;
                        - придумывать причины, последствия или обстоятельства;
                        - добавлять предположения и догадки;
                        - изменять даты, числа, имена, названия организаций, игр, мест и другие конкретные данные;
                        - утверждать то, чего прямо нет в исходной новости;
                        - превращать предположение в утверждение;
                        - добавлять информацию на основании собственных знаний;
                        - использовать эмоциональный стиль как повод придумать новые события.

                        РАЗРЕШЕНО:
                        - менять эмоциональный тон;
                        - использовать эмоциональные слова и выражения;
                        - менять порядок предложений;
                        - использовать метафоры и иронию, если они НЕ создают новых фактов;
                        - делать позитивную, негативную или ироничную подачу одного и того же фактического содержания.

                        ВАЖНО:
                        Если для создания определённой эмоции необходимо добавить новый факт, такой факт добавлять НЕЛЬЗЯ.
                        В этом случае нужно выразить эмоцию только за счёт формулировки существующих фактов.

                        Сначала выдели факты исходной новости.
                        Затем создай четыре версии новости: positive, negative, neutral и ironic.

                        Факты каждой версии должны содержать только факты, присутствующие в исходной новости.

                        Формат ответа:

                        {
                            \"original_facts\": [],
                            \"emotions\": {
                                \"positive\": {
                                    \"text\": '',
                                    \"facts\": []
                                },
                                \"negative\": {
                                    \"text\": '',
                                    \"facts\": []
                                },
                                \"neutral\": {
                                    \"text\": '',
                                    \"facts\": []
                                },
                                \"ironic\": {
                                    \"text\": '',
                                    \"facts\": []
                                }
                            }
                        }

                        Исходная новость:

                        $article",
        ];
    }
}
