<?php

namespace App\Http\Controllers\Composer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ArchivesController extends Controller
{
    public function __invoke(Request $request, string $vendor, string $package, string $file)
    {
        return response()->file(storage_path("app/private/satis/archives/{$vendor}/{$package}/{$file}"));
    }
}
