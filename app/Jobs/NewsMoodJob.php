<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\AiNewsService;
use Illuminate\Queue\Middleware\RateLimited;
use App\Models\News;
use App\Models\NewsMood;
use Illuminate\Support\Facades\Log;

class NewsMoodJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 30;
    public int $newsId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $newsId)
    {
        $this->newsId = $newsId;
    }

    public function middleware(): array {
        return [new RateLimited('ai-mood')];
    }

    /**
     * Execute the job.
     */
    public function handle(AiNewsService $aiService): void
    {
        $news = News::find($this->newsId);

        if (!$news || $news->status !== 'no_mod') {
            return; 
        }

        $results = $aiService->getMoodNews([[
            'id' => $news->id,
            'text' => $news->text,
        ]]);

        $result = $results[0] ?? null;

        if (!$result || $result['status'] === 'fail') {
            Log::channel('ai_mood')->warning('AI mood failed', [
                'news_id' => $news->id,
                'msg' => $result['msg'] ?? 'unknown error',
            ]);
            return;
        }

        foreach ($result['data']['emotions'] as $mood => $emotion) {
            NewsMood::create([
                'news_id' => $news->id,
                'mood' => $mood,
                'text' => $emotion['text'],
                'facts' => json_encode($emotion['facts'], JSON_UNESCAPED_UNICODE),
            ]);
        }

        $news->update([
            'status' => 'mod',
            'facts' => $result['data']['original_facts'],
        ]);

        Log::channel('ai_mood')->info('News processed', ['news_id' => $news->id]);

        $this->logFactsForManualReview($news->id, $result['data']);
    }

    private function logFactsForManualReview(int $newsId, array $data): void {
        $emotionsFacts = [];

        foreach ($data['emotions'] as $mood => $emotion) {
            $emotionsFacts[$mood] = $emotion['facts'];
        }

        Log::channel('facts_check')->info('Facts passed check', [
            'time' => now()->toDateTimeString(),
            'news_id' => $newsId,
            'original_facts' => $data['original_facts'],
            'emotions_facts' => $emotionsFacts,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::channel('ai_mood')->error('Job failed permanently', [
            'news_id' => $this->newsId,
            'error' => $e->getMessage(),
        ]);
    }
}
