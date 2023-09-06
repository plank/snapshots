<?php

namespace Plank\Snapshots\Tests\Database\Seeders\Model;

use Illuminate\Database\Seeder;
use Plank\Snapshots\Tests\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@app.test',
        ]);

        User::factory()->create([
            'name' => 'Editor',
            'email' => 'editor@app.test',
        ]);

        User::factory()->create([
            'name' => 'Visitor',
            'email' => 'visitor@app.test',
        ]);
    }
}
