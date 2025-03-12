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

        $satisConfig['homepage'] = config('app.url');

        $licenses = License::query()
            ->whereIn('type', [LicenseType::Composer, LicenseType::Github])
            ->get();

        $repositories = $licenses->map(
            fn (License $license) => [
                'type' => match ($license->type) {
                    LicenseType::Composer => 'composer',
                    LicenseType::Github => 'vcs',
                },
                'url' => $license->url,
                'options' => match ($license->type) {
                    LicenseType::Composer => [
                        'http' => [
                            'header' => [
                                'Authorization: Basic '.base64_encode("{$license->username}:{$license->password}"),
                            ],
                        ],
                    ],
                    LicenseType::Github => [
                        'github-oauth' => [
                            'github.com' => $license->password,
                        ],
                    ]
                },
            ]
        );

        $satisConfig['repositories'] = $repositories->toArray();

        $require = $licenses->mapWithKeys(
            fn (License $license) => [$license->name => '*']
        );

        if ($require->isNotEmpty()) {
            $satisConfig['require'] = (object) $require->toArray();
        }

        $configPath = storage_path('app/private/satis/config.json');

        $filesystem->ensureDirectoryExists(dirname($configPath));
        $filesystem->put($configPath, json_encode($satisConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info('Satis configuration file generated successfully');
        $this->warn('Building satis repository...');

        $process = Process::timeout(600)->run("php vendor/bin/satis build $configPath");

        $filesystem->delete($configPath);

        if ($process->failed()) {
            $this->error('Failed to build satis repository.');
            $this->error($process->errorOutput());

            return self::FAILURE;
        }

        $this->info('Satis repository built successfully!');

        return self::SUCCESS;
    }
}
