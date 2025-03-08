<?php

namespace App\Http\Controllers\Composer;

use Illuminate\Http\Request;

class ArchivesController
{
    public function __invoke(Request $request, string $vendor, string $package, string $file)
    {
        return response()->file(storage_path("app/private/satis/archives/{$vendor}/{$package}/{$file}"));
    }
}
