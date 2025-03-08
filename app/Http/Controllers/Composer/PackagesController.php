<?php

namespace App\Http\Controllers\Composer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class PackagesController
{
    public function __invoke(Request $request)
    {
        $packages = File::json(storage_path('app/private/satis/packages.json'));

        return response()->json($packages, 200);
    }
}
