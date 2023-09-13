<?php

namespace Plank\Snapshots\Tests\Database\Seeders\Relation;

use Illuminate\Database\Seeder;
use Plank\Snapshots\Tests\Models\Contractor;
use Plank\Snapshots\Tests\Models\Plan;
use Plank\Snapshots\Tests\Models\Project;
use Plank\Snapshots\Tests\Models\Task;

class MorphPivotSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PivotSeeder::class,
        ]);

        // Seed some contractors
        $can = Contractor::factory()->create([
            'name' => 'Canacorp',
        ]);

        $us = Contractor::factory()->create([
            'name' => 'Americorp',
        ]);

        $uk = Contractor::factory()->create([
            'name' => 'Anglocorp',
        ]);

        $wellington = Project::query()
            ->where('name', 'Wellington St.')
            ->first();

        $hvac = Plan::factory()->create([
            'name' => 'HVAC Blueprint',
        ]);

        $wellington->contractors()->attach($can);

        $wellington->plans()->attach($hvac->id, [
            'accepted' => true,
        ]);

        $installFurnace = Task::factory()->create([
            'project_id' => $wellington->id,
            'name' => 'Install new Furnace',
        ]);

        $installFurnace->contractors()->attach($can);

        $pennsylvania = Project::query()
            ->where('name', 'Pennsylvania Ave.')
            ->first();

        $pennsylvania->contractors()->attach($us);

        $electrical = Plan::factory()->create([
            'name' => 'Electrical Blueprint',
        ]);

        $pennsylvania->plans()->attach($electrical->id, [
            'accepted' => false,
        ]);

        $installLightbulb = Task::factory()->create([
            'project_id' => $pennsylvania->id,
            'name' => 'Install new Lightbulb',
        ]);

        $installLightbulb->contractors()->attach($us);

        $downing = Project::query()
            ->where('name', 'Downing St.')
            ->first();

        $downing->contractors()->attach($uk);

        $plumbing = Plan::factory()->create([
            'name' => 'Plumbing Blueprint',
        ]);

        $downing->plans()->attach($plumbing->id, [
            'accepted' => true,
        ]);

        $installToilet = Task::factory()->create([
            'project_id' => $downing->id,
            'name' => 'Install new Toilet',
        ]);

        $installToilet->contractors()->attach($uk);

        Plan::factory()->create([
            'name' => 'Networking Blueprint',
        ]);
    }
}
