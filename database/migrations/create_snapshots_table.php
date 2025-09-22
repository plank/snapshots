<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('previous_snapshot_id')->nullable()->index();
            $table->string('number')->unique();
            $table->boolean('migrated', false);
            $table->boolean('copied', false);
            $table->timestamps();

            $table->foreign('previous_snapshot_id')
                ->references('id')
                ->on('snapshots')
                ->nullOnDelete();
        });
    }
};
