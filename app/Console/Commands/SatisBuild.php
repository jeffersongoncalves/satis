<?php

namespace App\Console\Commands;

use App\Jobs\BuildSatisForTeamJob;
use App\Models\Team;
use Illuminate\Console\Command;

class SatisBuild extends Command
{
    protected $signature = 'satis:build';

    protected $description = 'Builds the Satis repository for all teams';

    public function handle(): int
    {
        $teams = Team::query()
            ->whereHas('packages')
            ->get();

        foreach ($teams as $team) {
            dispatch(new BuildSatisForTeamJob($team));
        }

        return self::SUCCESS;
    }
}
