<?php

namespace Database\Seeders;

use App\Console\Commands\SyncPhpReleases;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        Artisan::call(SyncPhpReleases::class);
    }
}
