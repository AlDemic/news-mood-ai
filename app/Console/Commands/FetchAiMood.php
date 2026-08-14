<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\News;
use App\Jobs\NewsMoodJob;


#[Signature('app:fetch-ai-mood')]
#[Description('Modified original news to different mood')]
class FetchAiMood extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $newsIds = News::query()
            ->where('status', 'no_mod')
            ->pluck('id');

        if ($newsIds->isEmpty()) {
            $this->info('No unmodified news found.');
            return;
        }

        foreach ($newsIds as $id) {
            NewsMoodJob::dispatch($id);
        }

        $this->info("Dispatched {$newsIds->count()} jobs.");
    }
}
