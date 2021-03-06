<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReleasesTable extends Migration
{
    public function up()
    {
        Schema::create('releases', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('major');
            $table->integer('minor');
            $table->integer('release');
            $table->dateTime('tagged_at');
            $table->dateTime('active_support_until');
            $table->dateTime('security_support_until');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('releases');
    }
}
