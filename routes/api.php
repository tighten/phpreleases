<?php

use App\Http\Controllers\ReleaseController;
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

Route::get('releases', [ReleaseController::class, 'index']);

Route::get('releases/minimum-supported/{supportType}', [ReleaseController::class, 'minimumSupported']);

Route::get('releases/latest', [ReleaseController::class, 'showLatest']);

Route::get('releases/{release}', [ReleaseController::class, 'show']);

Route::get('versions', [ReleaseController::class, 'index']);

Route::get('versions/minimum-supported/{supportType}', [ReleaseController::class, 'minimumSupported']);

Route::get('versions/latest', [ReleaseController::class, 'showLatest']);

Route::get('versions/{release}', [ReleaseController::class, 'show']);
