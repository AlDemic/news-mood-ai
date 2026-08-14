<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\News;
use App\Services\NewsService;
use Illuminate\Support\Facades\Log;

class FetchNewsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 30;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(NewsService $service): void
    {
        $data = $service->getNews();

        if ($data['status'] === 'fail') {
            Log::channel('news_fetch')->error('Fetch news failed', [
                'msg' => $data['msg'],
            ]);
            return;
        }

        $newRecords = $this->addToDb($data['articles']);

        Log::channel('news_fetch')->info('News fetched', [
            'new_records' => $newRecords,
        ]);
    }

    private function addToDb(array $articles): int {
        $newRecordsCounter = 0;

        foreach ($articles as $a) {
            $news = News::firstOrCreate($a);

            if ($news->wasRecentlyCreated) {
                $newRecordsCounter++;
            }
        }

        return $newRecordsCounter;
    }

    public function failed(\Throwable $e): void
    {
        Log::channel('news_fetch')->error('Job failed permanently', [
            'error' => $e->getMessage(),
        ]);
    }
}
