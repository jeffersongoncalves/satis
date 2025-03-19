<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class SatisClean extends Command
{
    protected $signature = 'satis:clean';

    protected $description = 'Cleans all the Satis repositories';

    public function handle(Filesystem $filesystem): int
    {
        $filesystem->deleteDirectory(storage_path('app/private/satis'));

        $this->info('Satis repositories cleaned successfully.');

        return self::SUCCESS;
    }
}
