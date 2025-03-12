<?php

namespace App\Http\Controllers\Composer;

use Illuminate\Support\Facades\File;

class PackagesV2Controller
{
    public function __invoke(string $vendor, string $package)
    {
        $package = File::json(storage_path("app/private/satis/p2/{$vendor}/{$package}.json"));

        return response()->json($package, 200);
    }
}
