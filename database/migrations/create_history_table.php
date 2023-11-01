<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('history', function (Blueprint $table) {
            $table->id();
            $table->morphs('causer');
            $table->morphs('trackable');
            $table->foreignId('version_id')->nullable()->constrained('versions');
            $table->string('operation');
            $table->json('from')->nullable();
            $table->json('to')->nullable();
            $table->timestamps();
        });
    }
};
