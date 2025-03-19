<?php

namespace App\Jobs;

use App\Data\Package as PackageData;
use App\Data\Repository as RepositoryData;
use App\Data\SatisConfig;
use App\Enums\PackageType;
use App\Models\Package;
use App\Models\Team;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Process;
use MichaelLedin\LaravelJob\FromParameters;
use RuntimeException;

class BuildSatisForTeamJob implements FromParameters, ShouldQueue
{
    use Queueable;

    public function __construct(protected Team $team) {}

    public function handle(): void
    {
        $config = SatisConfig::make();
        $config->homepage(config('app.url'));

        /** @var Package $package */
        foreach ($this->getPackages() as $package) {
            $config->repository(
                new RepositoryData(
                    type: $this->getRepositoryType($package),
                    url: $this->getRepositoryUrl($package),
                    options: $this->getRepositoryOptions($package)
                )
            );

            $config->require(
                new PackageData(name: $package->name)
            );
        }

        $config->merge(
            SatisConfig::load(base_path('satis.json'))
        );

        $config->saveAs(
            storage_path("app/private/satis/{$this->team->id}/config.json")
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

    private function getPackages(): Collection
    {
        return Package::query()
            ->whereBelongsTo($this->team)
            ->whereIn('type', [PackageType::Composer, PackageType::Github]) // TODO: Remove
            ->get();
    }

    private function getRepositories(Collection $packages): array
    {
        return $packages->map(
            fn (Package $package) => [
                'type' => $this->getRepositoryType($package),
                'url' => $this->getRepositoryUrl($package),
                'options' => $this->getRepositoryOptions($package),
            ]
        )->toArray();
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

    private function getRequires(Collection $packages): array
    {
        return $packages->mapWithKeys(
            fn (Package $package) => [$package->name => '*']
        )->toArray();
    }

    public static function fromParameters(string ...$parameters)
    {
        return new self(Team::findOrFail($parameters[0]));
    }
}
