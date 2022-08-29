<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHitsTable extends Migration
{
    public function up()
    {
        Schema::create('hits', function (Blueprint $table) {
            $table->id();
            $table->string('endpoint');
            $table->string('user_agent');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hits');
    }
}
