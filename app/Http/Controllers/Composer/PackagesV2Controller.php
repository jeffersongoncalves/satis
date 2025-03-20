<?php

namespace App\Http\Controllers\Composer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class PackagesV2Controller
{
    public function __invoke(Request $request, string $vendor, string $package)
    {
        $package = File::json(storage_path("app/private/satis/{$request->user()->id}/p2/{$vendor}/{$package}.json"));

        return response()->json($package, 200);
    }
}
