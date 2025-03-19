<?php

namespace App\Jobs;

use App\Data\Package as PackageData;
use App\Data\Repository as RepositoryData;
use App\Data\SatisConfig;
use App\Enums\PackageType;
use App\Models\Package;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Process;
use RuntimeException;

class SyncPackage implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Package $package) {}

    public function handle(): void
    {
        $config = SatisConfig::make();
        $config->homepage(config('app.url'));
        $config->outputDir(storage_path("app/private/satis/{$this->package->team_id}/"));

        $config->repository(
            new RepositoryData(
                type: $this->getRepositoryType($this->package),
                url: $this->getRepositoryUrl($this->package),
                options: $this->getRepositoryOptions($this->package)
            )
        );

        $config->require(
            new PackageData(name: $this->package->name)
        );

        $config->merge(
            SatisConfig::load(base_path('satis.json'))
        );

        $config->saveAs(
            storage_path("app/private/satis/{$this->package->team_id}/package-{$this->package->id}.json")
        );

        tap(
            Process::timeout(600)->run("php vendor/bin/satis build {$config->path}"),
            function (ProcessResult $process) {
                if ($process->successful()) {
                    return;
                }

                $this->fail(new RuntimeException($process->errorOutput()));
            }
        );

        $config->delete();
    }

    private function getRepositoryType(Package $package): string
    {
        return match ($package->type) {
            PackageType::Composer => 'composer',
            PackageType::Github => 'vcs',
        };
    }

    private function getRepositoryUrl(Package $package): string
    {
        return match ($package->type) {
            PackageType::Github => str($package->url)
                ->prepend('https://')
                ->replaceFirst('git@', "{$package->username}:{$package->password}@")
                ->replaceLast(':', '/')
                ->toString(),
            default => $package->url
        };
    }

    private function getRepositoryOptions(Package $package): array
    {
        return match ($package->type) {
            PackageType::Composer => [
                'http' => [
                    'header' => [
                        'Authorization: Basic '.base64_encode("{$package->username}:{$package->password}"),
                    ],
                ],
            ],
            default => [],
        };
    }
}
