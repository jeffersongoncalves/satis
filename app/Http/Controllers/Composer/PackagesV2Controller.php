<?php

namespace App\Http\Controllers\Composer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class PackagesV2Controller extends Controller
{
    public function __invoke(Request $request, string $vendor, string $package)
    {
        $packages = File::json(storage_path("app/private/satis/p2/{$vendor}/{$package}.json"));

        return response()->json($packages, 200);
    }
}
