<?php

namespace Plank\Snapshots\Tests\Database\Seeders\Relation;

use Illuminate\Database\Seeder;
use Plank\Snapshots\Tests\Models\Category;
use Plank\Snapshots\Tests\Models\Product;
use Plank\Snapshots\Tests\Models\Project;

class PivotSeeder extends Seeder
{
    public function run(): void
    {
        $mechanical = Category::factory()->create([
            'name' => 'Mechanical',
        ]);

        $eletrical = Category::factory()->create([
            'name' => 'Electrical',
        ]);

        $plumbing = Category::factory()->create([
            'name' => 'Plumbing',
        ]);

        // Mechanical products
        $furnace = Product::factory()->create([
            'name' => 'Furnace',
        ]);

        $fan = Product::factory()->create([
            'name' => 'Fan',
        ]);

        $heatPump = Product::factory()->create([
            'name' => 'Heat Pump',
        ]);

        // Electrical products
        $lightbulb = Product::factory()->create([
            'name' => 'Lightbulb',
        ]);

        $outlet = Product::factory()->create([
            'name' => 'Outlet',
        ]);

        $switch = Product::factory()->create([
            'name' => 'Switch',
        ]);

        // Plumbing products
        $toilet = Product::factory()->create([
            'name' => 'Toilet',
        ]);

        $sink = Product::factory()->create([
            'name' => 'Sink',
        ]);

        $shower = Product::factory()->create([
            'name' => 'Shower',
        ]);

        $wellington = Project::factory()->create([
            'name' => 'Wellington St.',
        ]);

        $wellington->categories()->attach($mechanical);

        $wellington->products()->attach($furnace, [
            'quantity' => 2,
        ]);

        $wellington->products()->attach($fan, [
            'quantity' => 3,
        ]);

        $pennsylvania = Project::factory()->create([
            'name' => 'Pennsylvania Ave.',
        ]);

        $pennsylvania->categories()->attach($eletrical);

        $pennsylvania->products()->attach($lightbulb, [
            'quantity' => 10,
        ]);

        $pennsylvania->products()->attach($outlet, [
            'quantity' => 5,
        ]);

        $downing = Project::factory()->create([
            'name' => 'Downing St.',
        ]);

        $downing->categories()->attach($plumbing);

        $downing->products()->attach($toilet, [
            'quantity' => 3,
        ]);
    }
}
