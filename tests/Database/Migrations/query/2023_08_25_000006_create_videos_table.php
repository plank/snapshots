<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->string('name');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();

            // We cannot logically add an fk from unversioned
            // content to versioned content
            // $table->foreign('post_id')->references('uuid')->on('posts');
        });
    }
};
