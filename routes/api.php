<?php

use App\Http\Controllers\VersionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('versions', [VersionController::class, 'index']);

Route::get('versions/minimum-supported/{supportType}', [VersionController::class, 'minimumSupported']);

Route::get('versions/latest', [VersionController::class, 'showLatest']);

Route::get('versions/{version}', [VersionController::class, 'show']);
