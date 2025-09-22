<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('existences', function (Blueprint $table) {
            $table->id();
            $table->morphs('trackable');
            $table->foreignId('version_id')->nullable()->constrained('versions');
            $table->string('hash')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('existences');
    }
};
