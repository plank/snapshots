<?php

use Plank\Snapshots\Tests\Database\Seeders\Relation\MorphPivotSeeder;
use Plank\Snapshots\Tests\Models\Contractor;
use Plank\Snapshots\Tests\Models\Plan;
use Plank\Snapshots\Tests\Models\Project;

use function Pest\Laravel\artisan;
use function Pest\Laravel\seed;

describe('Custom snapshotted MorphPivot classes use snapshotted tables correctly', function () {
    beforeEach(function () {
        artisan('migrate', [
            '--path' => migrationPath('pivot'),
            '--realpath' => true,
        ])->run();

        seed(MorphPivotSeeder::class);
    });

    it('can attach snapshotted models to snapshotted models on morph pivots', function () {
        snapshots()->setActive(createFirstSnapshot('pivot'));

        $wellington = Project::query()
            ->where('name', 'Wellington St.')
            ->first();

        $plans = $wellington->plans()->get()->pluck('name');

        expect($plans)->toContain('HVAC Blueprint');
        expect($plans)->not->toContain('Electrical Blueprint');
        expect($plans)->not->toContain('Plumbing Blueprint');
        expect($plans)->not->toContain('Networking Blueprint');

        snapshots()->setActive(createMinorSnapshot('pivot'));

        $networkingPlan = Plan::query()
            ->where('name', 'Networking Blueprint')
            ->first();

        $wellington->plans()->attach($networkingPlan->id, [
            'accepted' => true,
        ]);

        $plans = $wellington->plans()->get()->pluck('name');

        expect($plans)->toContain('HVAC Blueprint');
        expect($plans)->not->toContain('Electrical Blueprint');
        expect($plans)->not->toContain('Plumbing Blueprint');
        expect($plans)->toContain('Networking Blueprint');

        snapshots()->setActive(snapshots()->byKey('1.0.0'));

        $plans = $wellington->plans()->get()->pluck('name');

        expect($plans)->toContain('HVAC Blueprint');
        expect($plans)->not->toContain('Electrical Blueprint');
        expect($plans)->not->toContain('Plumbing Blueprint');
        expect($plans)->not->toContain('Networking Blueprint');
    });

    it('can detach snapshotted models to snapshotted models on morph pivots', function () {
        snapshots()->setActive(createFirstSnapshot('pivot'));

        $wellington = Project::query()
            ->where('name', 'Wellington St.')
            ->first();

        $plans = $wellington->plans()->get()->pluck('name');

        expect($plans)->toContain('HVAC Blueprint');
        expect($plans)->not->toContain('Electrical Blueprint');
        expect($plans)->not->toContain('Plumbing Blueprint');
        expect($plans)->not->toContain('Networking Blueprint');

        snapshots()->setActive(createMinorSnapshot('pivot'));

        $hvacPlan = Plan::query()
            ->where('name', 'HVAC Blueprint')
            ->first();

        $wellington->plans()->detach($hvacPlan->id);

        $plans = $wellington->plans()->get()->pluck('name');

        expect($plans)->not->toContain('HVAC Blueprint');
        expect($plans)->not->toContain('Electrical Blueprint');
        expect($plans)->not->toContain('Plumbing Blueprint');
        expect($plans)->not->toContain('Networking Blueprint');

        snapshots()->setActive(snapshots()->byKey('1.0.0'));

        $plans = $wellington->plans()->get()->pluck('name');

        expect($plans)->toContain('HVAC Blueprint');
        expect($plans)->not->toContain('Electrical Blueprint');
        expect($plans)->not->toContain('Plumbing Blueprint');
        expect($plans)->not->toContain('Networking Blueprint');
    });

    it('can delete the pivot for snapshotted models to snapshotted models on morph pivots', function () {
        snapshots()->setActive(createFirstSnapshot('pivot'));

        $wellington = Project::query()
            ->where('name', 'Wellington St.')
            ->first();

        $plans = $wellington->plans()->get()->pluck('name');

        expect($plans)->toContain('HVAC Blueprint');
        expect($plans)->not->toContain('Electrical Blueprint');
        expect($plans)->not->toContain('Plumbing Blueprint');
        expect($plans)->not->toContain('Networking Blueprint');

        snapshots()->setActive(createMinorSnapshot('pivot'));

        $hvacPlan = Plan::query()
            ->where('name', 'HVAC Blueprint')
            ->first();

        $wellington->plans()->where('name', 'HVAC Blueprint')->first()->pivot->delete();

        $plans = $wellington->plans()->get()->pluck('name');

        expect($plans)->not->toContain('HVAC Blueprint');
        expect($plans)->not->toContain('Electrical Blueprint');
        expect($plans)->not->toContain('Plumbing Blueprint');
        expect($plans)->not->toContain('Networking Blueprint');

        snapshots()->setActive(snapshots()->byKey('1.0.0'));

        $plans = $wellington->plans()->get()->pluck('name');

        expect($plans)->toContain('HVAC Blueprint');
        expect($plans)->not->toContain('Electrical Blueprint');
        expect($plans)->not->toContain('Plumbing Blueprint');
        expect($plans)->not->toContain('Networking Blueprint');
    });

    it('can sync snapshotted models to snapshotted models on morph pivots', function () {
        snapshots()->setActive(createFirstSnapshot('pivot'));

        $wellington = Project::query()
            ->where('name', 'Wellington St.')
            ->first();

        $plans = $wellington->plans()->get()->pluck('name');

        expect($plans)->toContain('HVAC Blueprint');
        expect($plans)->not->toContain('Electrical Blueprint');
        expect($plans)->not->toContain('Plumbing Blueprint');
        expect($plans)->not->toContain('Networking Blueprint');

        snapshots()->setActive(createMinorSnapshot('pivot'));

        $electricalPlan = Plan::query()
            ->where('name', 'Electrical Blueprint')
            ->first();

        $plumbingPlan = Plan::query()
            ->where('name', 'Plumbing Blueprint')
            ->first();

        $networkingPlan = Plan::query()
            ->where('name', 'Networking Blueprint')
            ->first();

        $wellington->plans()->sync([
            $electricalPlan->id => [
                'accepted' => true,
            ],
            $plumbingPlan->id => [
                'accepted' => true,
            ],
            $networkingPlan->id => [
                'accepted' => true,
            ],
        ]);

        $plans = $wellington->plans()->get()->pluck('name');

        expect($plans)->not->toContain('HVAC Blueprint');
        expect($plans)->toContain('Electrical Blueprint');
        expect($plans)->toContain('Plumbing Blueprint');
        expect($plans)->toContain('Networking Blueprint');

        snapshots()->setActive(snapshots()->byKey('1.0.0'));

        $plans = $wellington->plans()->get()->pluck('name');

        expect($plans)->toContain('HVAC Blueprint');
        expect($plans)->not->toContain('Electrical Blueprint');
        expect($plans)->not->toContain('Plumbing Blueprint');
        expect($plans)->not->toContain('Networking Blueprint');
    });

    it('can sync without detaching snapshotted models to snapshotted models on morph pivots', function () {
        snapshots()->setActive(createFirstSnapshot('pivot'));

        $wellington = Project::query()
            ->where('name', 'Wellington St.')
            ->first();

        $plans = $wellington->plans()->get()->pluck('name');

        expect($plans)->toContain('HVAC Blueprint');
        expect($plans)->not->toContain('Electrical Blueprint');
        expect($plans)->not->toContain('Plumbing Blueprint');
        expect($plans)->not->toContain('Networking Blueprint');

        snapshots()->setActive(createMinorSnapshot('pivot'));

        $electricalPlan = Plan::query()
            ->where('name', 'Electrical Blueprint')
            ->first();

        $plumbingPlan = Plan::query()
            ->where('name', 'Plumbing Blueprint')
            ->first();

        $networkingPlan = Plan::query()
            ->where('name', 'Networking Blueprint')
            ->first();

        $wellington->plans()->syncWithoutDetaching([
            $electricalPlan->id => [
                'accepted' => true,
            ],
            $plumbingPlan->id => [
                'accepted' => true,
            ],
            $networkingPlan->id => [
                'accepted' => true,
            ],
        ]);

        $plans = $wellington->plans()->get()->pluck('name');

        expect($plans)->toContain('HVAC Blueprint');
        expect($plans)->toContain('Electrical Blueprint');
        expect($plans)->toContain('Plumbing Blueprint');
        expect($plans)->toContain('Networking Blueprint');

        snapshots()->setActive(snapshots()->byKey('1.0.0'));

        $plans = $wellington->plans()->get()->pluck('name');

        expect($plans)->toContain('HVAC Blueprint');
        expect($plans)->not->toContain('Electrical Blueprint');
        expect($plans)->not->toContain('Plumbing Blueprint');
        expect($plans)->not->toContain('Networking Blueprint');
    });

    it('can attach snapshotted models to plain models on morph pivots', function () {
        snapshots()->setActive(createFirstSnapshot('pivot'));

        $wellington = Project::query()
            ->where('name', 'Wellington St.')
            ->first();

        $contractors = $wellington->contractors()->get()->pluck('name');

        expect($contractors)->toContain('Canacorp');
        expect($contractors)->not->toContain('Americorp');
        expect($contractors)->not->toContain('Anglocorp');

        snapshots()->setActive(createMinorSnapshot('pivot'));

        $mega = Contractor::factory()->create([
            'name' => 'Mega Canacorp',
        ]);

        $wellington->contractors()->attach($mega->id);

        $contractors = $wellington->contractors()->get()->pluck('name');

        expect($contractors)->toContain('Canacorp');
        expect($contractors)->not->toContain('Americorp');
        expect($contractors)->not->toContain('Anglocorp');
        expect($contractors)->toContain('Mega Canacorp');

        snapshots()->setActive(snapshots()->byKey('1.0.0'));

        $contractors = $wellington->contractors()->get()->pluck('name');

        expect($contractors)->toContain('Canacorp');
        expect($contractors)->not->toContain('Americorp');
        expect($contractors)->not->toContain('Anglocorp');
        expect($contractors)->not->toContain('Mega Canacorp');
    });

    it('can detach snapshotted models to plain models on morph pivots', function () {
        snapshots()->setActive(createFirstSnapshot('pivot'));

        $wellington = Project::query()
            ->where('name', 'Wellington St.')
            ->first();

        $contractors = $wellington->contractors()->get()->pluck('name');

        expect($contractors)->toContain('Canacorp');
        expect($contractors)->not->toContain('Americorp');
        expect($contractors)->not->toContain('Anglocorp');

        snapshots()->setActive(createMinorSnapshot('pivot'));

        $canacorp = Contractor::query()
            ->where('name', 'Canacorp')
            ->first();

        $wellington->contractors()->detach($canacorp->id);

        $contractors = $wellington->contractors()->get()->pluck('name');

        expect($contractors)->not->toContain('Canacorp');
        expect($contractors)->not->toContain('Americorp');
        expect($contractors)->not->toContain('Anglocorp');

        snapshots()->setActive(snapshots()->byKey('1.0.0'));

        $contractors = $wellington->contractors()->get()->pluck('name');

        expect($contractors)->toContain('Canacorp');
        expect($contractors)->not->toContain('Americorp');
        expect($contractors)->not->toContain('Anglocorp');
    });

    it('can delete the pivot for snapshotted models to plain models on morph pivots', function () {
        snapshots()->setActive(createFirstSnapshot('pivot'));

        $wellington = Project::query()
            ->where('name', 'Wellington St.')
            ->first();

        $contractors = $wellington->contractors()->get()->pluck('name');

        expect($contractors)->toContain('Canacorp');
        expect($contractors)->not->toContain('Americorp');
        expect($contractors)->not->toContain('Anglocorp');

        snapshots()->setActive(createMinorSnapshot('pivot'));

        $canacorp = Contractor::query()
            ->where('name', 'Canacorp')
            ->first();

        $wellington->contractors()->where('name', 'Canacorp')->first()->pivot->delete();

        $contractors = $wellington->contractors()->get()->pluck('name');

        expect($contractors)->not->toContain('Canacorp');
        expect($contractors)->not->toContain('Americorp');
        expect($contractors)->not->toContain('Anglocorp');

        snapshots()->setActive(snapshots()->byKey('1.0.0'));

        $contractors = $wellington->contractors()->get()->pluck('name');

        expect($contractors)->toContain('Canacorp');
        expect($contractors)->not->toContain('Americorp');
        expect($contractors)->not->toContain('Anglocorp');
    });

    it('can sync snapshotted models to plain models on morph pivots', function () {
        snapshots()->setActive(createFirstSnapshot('pivot'));

        $wellington = Project::query()
            ->where('name', 'Wellington St.')
            ->first();

        $contractors = $wellington->contractors()->get()->pluck('name');

        expect($contractors)->toContain('Canacorp');
        expect($contractors)->not->toContain('Americorp');
        expect($contractors)->not->toContain('Anglocorp');

        snapshots()->setActive(createMinorSnapshot('pivot'));

        $americorp = Contractor::query()
            ->where('name', 'Americorp')
            ->first();

        $anglocorp = Contractor::query()
            ->where('name', 'Anglocorp')
            ->first();

        $wellington->contractors()->sync([
            $americorp->id,
            $anglocorp->id,
        ]);

        $contractors = $wellington->contractors()->get()->pluck('name');

        expect($contractors)->not->toContain('Canacorp');
        expect($contractors)->toContain('Americorp');
        expect($contractors)->toContain('Anglocorp');

        snapshots()->setActive(snapshots()->byKey('1.0.0'));

        $contractors = $wellington->contractors()->get()->pluck('name');

        expect($contractors)->toContain('Canacorp');
        expect($contractors)->not->toContain('Americorp');
        expect($contractors)->not->toContain('Anglocorp');
    });

    it('can sync without detaching snapshotted models to plain models on morph pivots', function () {
        snapshots()->setActive(createFirstSnapshot('pivot'));

        $wellington = Project::query()
            ->where('name', 'Wellington St.')
            ->first();

        $contractors = $wellington->contractors()->get()->pluck('name');

        expect($contractors)->toContain('Canacorp');
        expect($contractors)->not->toContain('Americorp');
        expect($contractors)->not->toContain('Anglocorp');

        snapshots()->setActive(createMinorSnapshot('pivot'));

        $americorp = Contractor::query()
            ->where('name', 'Americorp')
            ->first();

        $anglocorp = Contractor::query()
            ->where('name', 'Anglocorp')
            ->first();

        $wellington->contractors()->syncWithoutDetaching([
            $americorp->id,
            $anglocorp->id,
        ]);

        $contractors = $wellington->contractors()->get()->pluck('name');

        expect($contractors)->toContain('Canacorp');
        expect($contractors)->toContain('Americorp');
        expect($contractors)->toContain('Anglocorp');

        snapshots()->setActive(snapshots()->byKey('1.0.0'));

        $contractors = $wellington->contractors()->get()->pluck('name');

        expect($contractors)->toContain('Canacorp');
        expect($contractors)->not->toContain('Americorp');
        expect($contractors)->not->toContain('Anglocorp');
    });

    it('can attach plain models to snapshotted models on MorphPivot on morph pivots', function () {
        snapshots()->setActive(createFirstSnapshot('pivot'));

        $can = Contractor::where('name', 'Canacorp')->first();

        $projects = $can->projects()->get()->pluck('name');

        expect($projects)->toContain('Wellington St.');
        expect($projects)->not->toContain('Pennsylvania Ave.');
        expect($projects)->not->toContain('Downing St.');

        snapshots()->setActive(createMajorSnapshot('pivot'));

        $pennsylvania = Project::query()
            ->where('name', 'Pennsylvania Ave.')
            ->first();

        $can->projects()->attach($pennsylvania->ulid);

        $projects = $can->projects()->get()->pluck('name');

        expect($projects)->toContain('Wellington St.');
        expect($projects)->toContain('Pennsylvania Ave.');
        expect($projects)->not->toContain('Downing St.');

        snapshots()->setActive(snapshots()->byKey('1.0.0'));

        $projects = $can->projects()->get()->pluck('name');

        expect($projects)->toContain('Wellington St.');
        expect($projects)->not->toContain('Pennsylvania Ave.');
        expect($projects)->not->toContain('Downing St.');
    });

    it('can detach plain models to snapshotted models on morph pivots', function () {
        snapshots()->setActive(createFirstSnapshot('pivot'));

        $can = Contractor::where('name', 'Canacorp')->first();

        $projects = $can->projects()->get()->pluck('name');

        expect($projects)->toContain('Wellington St.');
        expect($projects)->not->toContain('Pennsylvania Ave.');
        expect($projects)->not->toContain('Downing St.');

        snapshots()->setActive(createMajorSnapshot('pivot'));

        $wellington = Project::query()
            ->where('name', 'Wellington St.')
            ->first();

        $can->projects()->detach($wellington->ulid);

        $projects = $can->projects()->get()->pluck('name');

        expect($projects)->not->toContain('Wellington St.');
        expect($projects)->not->toContain('Pennsylvania Ave.');
        expect($projects)->not->toContain('Downing St.');

        snapshots()->setActive(snapshots()->byKey('1.0.0'));

        $projects = $can->projects()->get()->pluck('name');

        expect($projects)->toContain('Wellington St.');
        expect($projects)->not->toContain('Pennsylvania Ave.');
        expect($projects)->not->toContain('Downing St.');
    });

    it('can delete the pivot for plain models to snapshotted models on morph pivots', function () {
        snapshots()->setActive(createFirstSnapshot('pivot'));

        $can = Contractor::where('name', 'Canacorp')->first();

        $projects = $can->projects()->get()->pluck('name');

        expect($projects)->toContain('Wellington St.');
        expect($projects)->not->toContain('Pennsylvania Ave.');
        expect($projects)->not->toContain('Downing St.');

        snapshots()->setActive(createMajorSnapshot('pivot'));

        $wellington = Project::query()
            ->where('name', 'Wellington St.')
            ->first();

        $can->projects()->where('name', 'Wellington St.')->first()->pivot->delete();

        $projects = $can->projects()->get()->pluck('name');

        expect($projects)->not->toContain('Wellington St.');
        expect($projects)->not->toContain('Pennsylvania Ave.');
        expect($projects)->not->toContain('Downing St.');

        snapshots()->setActive(snapshots()->byKey('1.0.0'));

        $projects = $can->projects()->get()->pluck('name');

        expect($projects)->toContain('Wellington St.');
        expect($projects)->not->toContain('Pennsylvania Ave.');
        expect($projects)->not->toContain('Downing St.');
    });

    it('can sync plain models to snapshotted models on morph pivots', function () {
        snapshots()->setActive(createFirstSnapshot('pivot'));

        $can = Contractor::where('name', 'Canacorp')->first();

        $projects = $can->projects()->get()->pluck('name');

        expect($projects)->toContain('Wellington St.');
        expect($projects)->not->toContain('Pennsylvania Ave.');
        expect($projects)->not->toContain('Downing St.');

        snapshots()->setActive(createMajorSnapshot('pivot'));

        $pennsylvania = Project::query()
            ->where('name', 'Pennsylvania Ave.')
            ->first();

        $downing = Project::query()
            ->where('name', 'Downing St.')
            ->first();

        $can->projects()->sync([
            $pennsylvania->ulid,
            $downing->ulid,
        ]);

        $projects = $can->projects()->get()->pluck('name');

        expect($projects)->not->toContain('Wellington St.');
        expect($projects)->toContain('Pennsylvania Ave.');
        expect($projects)->toContain('Downing St.');

        snapshots()->setActive(snapshots()->byKey('1.0.0'));

        $projects = $can->projects()->get()->pluck('name');

        expect($projects)->toContain('Wellington St.');
        expect($projects)->not->toContain('Pennsylvania Ave.');
        expect($projects)->not->toContain('Downing St.');
    });

    it('can sync without detaching plain models to snapshotted models on morph pivots', function () {
        snapshots()->setActive(createFirstSnapshot('pivot'));

        $can = Contractor::where('name', 'Canacorp')->first();

        $projects = $can->projects()->get()->pluck('name');

        expect($projects)->toContain('Wellington St.');
        expect($projects)->not->toContain('Pennsylvania Ave.');
        expect($projects)->not->toContain('Downing St.');

        snapshots()->setActive(createMajorSnapshot('pivot'));

        $pennsylvania = Project::query()
            ->where('name', 'Pennsylvania Ave.')
            ->first();

        $downing = Project::query()
            ->where('name', 'Downing St.')
            ->first();

        $can->projects()->syncWithoutDetaching([
            $pennsylvania->ulid,
            $downing->ulid,
        ]);

        $projects = $can->projects()->get()->pluck('name');

        expect($projects)->toContain('Wellington St.');
        expect($projects)->toContain('Pennsylvania Ave.');
        expect($projects)->toContain('Downing St.');

        snapshots()->setActive(snapshots()->byKey('1.0.0'));

        $projects = $can->projects()->get()->pluck('name');

        expect($projects)->toContain('Wellington St.');
        expect($projects)->not->toContain('Pennsylvania Ave.');
        expect($projects)->not->toContain('Downing St.');
    });
});
