<?php

namespace App\Jobs;

use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Process\Exceptions\ProcessTimedOutException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Process;
use RuntimeException;

class ProcessSatisByPathAndRepositoryUrl implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

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
        $composer_path = storage_path('app/private/composer');
        rescue(fn () => mkdir(dirname($composer_path), 0755, true), report: false);
        try {
            tap(
                Process::forever()
                    ->env(['COMPOSER_HOME' => $composer_path])
                    ->run("php vendor/bin/satis build {$this->path} --repository-url " . $this->repositoryUrl),
                function (ProcessResult $process) {
                    if ($process->successful()) {
                        return;
                    }
                }
            );
        } catch (RuntimeException|ProcessTimedOutException) {
        }
    }
}
