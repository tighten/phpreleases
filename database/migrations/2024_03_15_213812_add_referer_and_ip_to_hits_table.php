<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hits', function (Blueprint $table) {
            $table->string('ip')->nullable()->after('user_agent');
            $table->string('referer')->nullable()->after('user_agent');
        });
    }
};
