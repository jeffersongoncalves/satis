<?php

namespace App\Http\Controllers\Composer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class PackagesController
{
    public function __invoke(Request $request)
    {
        if (! File::exists($path = storage_path("app/private/satis/{$request->user()->id}/packages.json"))) {
            return response()->noContent(404);
        }

        $packages = File::json($path);

        return response()->json($packages, 200);
    }
}
