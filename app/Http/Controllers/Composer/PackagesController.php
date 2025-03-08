<?php

namespace App\Http\Controllers\Composer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class PackagesController extends Controller
{
    public function __invoke(Request $request)
    {
        $packages = File::json(storage_path('app/private/satis/packages.json'));

        return response()->json($packages, 200);
    }
}
