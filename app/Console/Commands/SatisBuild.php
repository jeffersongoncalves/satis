<?php

namespace App\Console\Commands;

use App\Enums\LicenseType;
use App\Models\License;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Process;

class SatisBuild extends Command
{
    protected $signature = 'satis:build';

    protected $description = 'Builds the satis repository';

    public function handle(Filesystem $filesystem): int
    {
        $satisConfig = $filesystem->json(base_path('satis.json'));

        $licenses = License::query()
            ->where('type', LicenseType::Composer)
            ->get(['name', 'url', 'username', 'password']);

        $repositories = $licenses->map(
            fn (License $license) => [
                'type' => 'composer',
                'url' => $license->url,
                'options' => [
                    'http' => [
                        'header' => [
                            'Authorization: Basic '.base64_encode("{$license->username}:{$license->password}"),
                        ],
                    ],
                ],
            ]
        );

        $require = $licenses->mapWithKeys(
            fn (License $license) => [$license->name => '*']
        );

        $satisConfig['repositories'] = $repositories->toArray();
        $satisConfig['require'] = $require->toArray();

        $configPath = storage_path('app/private/satis/config.json');

        $filesystem->ensureDirectoryExists(dirname($configPath));
        $filesystem->put($configPath, json_encode($satisConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info('Satis configuration file generated successfully');
        $this->warn('Building satis repository...');

        $process = Process::run("php vendor/bin/satis build $configPath");

        if ($process->failed()) {
            $this->error('Failed to build satis repository.');
            $this->error($process->errorOutput());

            return self::FAILURE;
        }

        $this->info('Satis repository built successfully!');

        return self::SUCCESS;
    }
}
