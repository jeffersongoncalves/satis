<?php

namespace App\Http\Controllers\Composer;

use Illuminate\Support\Facades\File;

class PackagesController
{
    public function __invoke()
    {
        $packages = File::json(storage_path('app/private/satis/packages.json'));

        return response()->json($packages, 200);
    }
}
