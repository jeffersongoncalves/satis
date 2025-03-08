<?php

namespace App\Http\Controllers\Composer;

class ArchivesController
{
    public function __invoke(string $vendor, string $package, string $file)
    {
        return response()->file(storage_path("app/private/satis/archives/{$vendor}/{$package}/{$file}"));
    }
}
