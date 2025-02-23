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
        Schema::create('unversioneds', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('unversioned_alsos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Schema::create('unversioned_ulids', function (Blueprint $table) {
        //     $table->ulid('id')->primary();
        //     $table->string('name');
        //     $table->timestamps();
        // });

        // Schema::create('unversioned_ulid_alsos', function (Blueprint $table) {
        //     $table->ulid('id')->primary();
        //     $table->string('name');
        //     $table->timestamps();
        // });

        // Schema::create('unversioned_uuids', function (Blueprint $table) {
        //     $table->uuid('id')->primary();
        //     $table->string('name');
        //     $table->timestamps();
        // });

        // Schema::create('unversioned_uuid_alsos', function (Blueprint $table) {
        //     $table->uuid('id')->primary();
        //     $table->string('name');
        //     $table->timestamps();
        // });
    }
};
