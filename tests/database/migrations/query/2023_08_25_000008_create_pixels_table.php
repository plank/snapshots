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
        Schema::create('pixel', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->string('property');
            $table->string('value');
            $table->timestamps();

            $table->foreign('post_id')->references('id')->on('posts');
        });
    }
};

/**
  *** Versioned
  Post
  Seo

  * Polymorphic
  Tag M2M

  *** Unversioned:
  Role
  User
  Video
  Pixel

  * Polymorphic
  Media O2M


  HasManyThrough
  V -> V -> V
  V-> V -> U
  V -> U -> V
  U -> V -> V
  V -> U -> U
  U -> U -> V
  U -> V -> U


  |-----------------|-------------------|------------------|----------------|
  | Relation        | V to V            | V to U           | U to V         |
  |-----------------|-------------------|------------------|----------------|
  | HasOne          | Post -> Pixel     | Post -> Video    | User -> Bio    |
  | HasMany         | Post -> Seo       | Post -> ?        | User -> Post   |
  | HasManyThrough  |                   |                  |                |
  | BelongsTo       | Seo -> Post       | Post -> User     | Video -> Post  |
  | BelongsToMany   | Post -> Post      | ?                | ?              |
  | MorphTo         | Tag -> Post       | Tag -> Video     |  Media -> Post |
  | MorphMany       | Post -> Tag       | Post -> Media    |  Video -> Tag  |
  | MorphToMany     |                   |                  |                |
  | MorphedByMany   |                   |                  |                |
  | MorphOne        |                   |                  |                |
  |-----------------|-------------------|------------------|----------------|
*/
