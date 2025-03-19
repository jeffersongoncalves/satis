<?php

namespace App\Console\Commands;

use App\Enums\PackageType;
use App\Jobs\SyncPackage;
use App\Models\Package;
use Illuminate\Console\Command;

class SatisBuild extends Command
{
    protected $signature = 'satis:build';

    protected $description = 'Builds the Satis repository for all teams';

    public function handle(): int
    {
        $packages = Package::query()
            ->whereIn('type', [PackageType::Composer, PackageType::Github])
            ->get();

        foreach ($packages as $package) {
            dispatch(new SyncPackage($package));
        }

        return self::SUCCESS;
    }
}
