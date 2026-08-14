<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Jobs\FetchNewsJob;

#[Signature('app:fetch-news')]
#[Description('Get api news')]
class FetchNews extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        FetchNewsJob::dispatch();

        $this->info('News fetch job dispatched.');
    }
}
