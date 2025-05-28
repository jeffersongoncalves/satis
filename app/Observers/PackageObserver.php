<?php

namespace App\Observers;

use App\Jobs\SyncUsersByTeam;
use App\Models\Package;

class PackageObserver
{
    public function created(Package $package): void
    {
        SyncUsersByTeam::dispatch($package->team);
    }
}
