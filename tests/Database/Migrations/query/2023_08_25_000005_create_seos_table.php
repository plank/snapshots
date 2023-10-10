<?php

use Illuminate\Database\Schema\Blueprint;
use Plank\Snapshots\Migrator\SnapshotMigration;

return new class extends SnapshotMigration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->schema->create('seos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->string('attribute');
            $table->text('value');
            $table->timestamps();

            $table->foreign('post_id')->references('uuid')->on('posts');
        });
    }
};
