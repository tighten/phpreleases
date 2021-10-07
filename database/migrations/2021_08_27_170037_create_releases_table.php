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
            $table->timestamp('tagged_at');
            $table->timestamp('active_support_until');
            $table->timestamp('security_support_until');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('releases');
    }
}
