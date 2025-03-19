<?php

namespace App\Console\Commands;

use App\Enums\PackageType;
use App\Models\Package;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Process;

class SatisBuild extends Command
{
    protected $signature = 'satis:build';

    protected $description = 'Builds the Satis repository';

    public function handle(Filesystem $filesystem): int
    {
        $satisConfig = $filesystem->json(base_path('satis.json'));

        $satisConfig['homepage'] = config('app.url');

        $packages = Package::query()
            ->whereIn('type', [PackageType::Composer, PackageType::Github])
            ->get();

        $repositories = $packages->map(
            fn (Package $package) => [
                'type' => match ($package->type) {
                    PackageType::Composer => 'composer',
                    PackageType::Github => 'vcs',
                },
                'url' => $package->url,
                'options' => match ($package->type) {
                    PackageType::Composer => [
                        'http' => [
                            'header' => [
                                'Authorization: Basic '.base64_encode("{$package->username}:{$package->password}"),
                            ],
                        ],
                    ],
                    PackageType::Github => [
                        'http-basic' => [
                            'github.com' => [
                                'username' => $package->username,
                                'password' => $package->password,
                            ],
                        ],
                    ]
                },
            ]
        );

        $satisConfig['repositories'] = $repositories->toArray();

        $require = $packages->mapWithKeys(
            fn (Package $package) => [$package->name => '*']
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
