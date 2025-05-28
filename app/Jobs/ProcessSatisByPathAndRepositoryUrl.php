<?php

namespace App\Jobs;

use Exception;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Process;

class ProcessSatisByPathAndRepositoryUrl implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly string $path, private readonly string $repositoryUrl)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            tap(
                Process::timeout(3600)->run("php vendor/bin/satis build {$this->path} --repository-url " . $this->repositoryUrl),
                function (ProcessResult $process) {
                    if ($process->successful()) {
                        return;
                    }
                }
            );
        }catch (Exception){
        }
    }
}
