<?php

use App\Http\Controllers\Composer\ArchivesController;
use App\Http\Controllers\Composer\PackagesController;
use App\Http\Controllers\Composer\PackagesV2Controller;
use App\Http\Middleware\EnsureUserHasLicense;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth.basic', EnsureUserHasLicense::class]], function (): void {
    Route::get('packages.json', PackagesController::class)->withoutMiddleware(EnsureUserHasLicense::class);
    Route::get('p2/{vendor}/{package}.json', PackagesV2Controller::class);
    Route::get('archives/{vendor}/{package}/{file}', ArchivesController::class)->where('file', '.*\.zip');
});
