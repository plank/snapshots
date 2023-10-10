<?php

use Plank\Snapshots\Tests\Database\Seeders\Relation\PivotSeeder;
use Plank\Snapshots\Tests\Models\Category;
use Plank\Snapshots\Tests\Models\Product;
use Plank\Snapshots\Tests\Models\Project;

use function Pest\Laravel\artisan;
use function Pest\Laravel\seed;

describe('Custom versioned Pivot classes use version tables correctly', function () {
    beforeEach(function () {
        artisan('migrate', [
            '--path' => migrationPath('pivot'),
            '--realpath' => true,
        ])->run();

        seed(PivotSeeder::class);
    });

    it('can attach versioned models to versioned models', function () {
        versions()->setActive(createFirstVersion('pivot'));

        $project = Project::query()
            ->where('name', 'Wellington St.')
            ->first();

        $products = $project->products->pluck('name');

        expect($products)->toContain('Furnace');
        expect($products)->toContain('Fan');
        expect($products)->not()->toContain('Heat Pump');

        versions()->setActive(releaseAndCreatePatchVersion('pivot'));

        $heatPump = Product::query()
            ->where('name', 'Heat Pump')
            ->first();

        $project->products()->attach([
            $heatPump->id => [
                'quantity' => 1,
            ],
        ]);

        $products = $project->activeVersion()->products->pluck('name');

        expect($products)->toContain('Furnace');
        expect($products)->toContain('Fan');
        expect($products)->toContain('Heat Pump');

        versions()->setActive(versions()->byNumber('1.0.0'));

        $products = $project->activeVersion()->products->pluck('name');

        expect($products)->toContain('Furnace');
        expect($products)->toContain('Fan');
        expect($products)->not()->toContain('Heat Pump');
    });

    it('can detach versioned models to versioned models', function () {
        versions()->setActive(createFirstVersion('pivot'));

        $project = Project::query()
            ->where('name', 'Pennsylvania Ave.')
            ->first();

        $products = $project->products->pluck('name');

        expect($products)->toContain('Lightbulb');
        expect($products)->toContain('Outlet');
        expect($products)->not()->toContain('Switch');

        versions()->setActive(releaseAndCreatePatchVersion('pivot'));

        $project->products()->detach([
            Product::query()
                ->where('name', 'Lightbulb')
                ->first()
                ->id,
        ]);

        $products = $project->activeVersion()->products->pluck('name');

        expect($products)->not()->toContain('Lightbulb');
        expect($products)->toContain('Outlet');
        expect($products)->not()->toContain('Switch');

        versions()->setActive(versions()->byNumber('1.0.0'));

        $products = $project->activeVersion()->products->pluck('name');

        expect($products)->toContain('Lightbulb');
        expect($products)->toContain('Outlet');
        expect($products)->not()->toContain('Switch');
    });

    it('can delete the pivot for versioned models to versioned models', function () {
        versions()->setActive(createFirstVersion('pivot'));

        $project = Project::query()
            ->where('name', 'Downing St.')
            ->first();

        $products = $project->products->pluck('name');

        expect($products)->toContain('Toilet');
        expect($products)->not()->toContain('Sink');
        expect($products)->not()->toContain('Shower');

        versions()->setActive(releaseAndCreatePatchVersion('pivot'));

        $project->products()->where('name', 'Toilet')->first()->pivot->delete();

        $products = $project->activeVersion()->products->pluck('name');

        expect($products)->not()->toContain('Toilet');
        expect($products)->not()->toContain('Sink');
        expect($products)->not()->toContain('Shower');

        versions()->setActive(versions()->byNumber('1.0.0'));

        $products = $project->activeVersion()->products->pluck('name');

        expect($products)->toContain('Toilet');
        expect($products)->not()->toContain('Sink');
        expect($products)->not()->toContain('Shower');
    });

    it('can sync versioned models to versioned models', function () {
        versions()->setActive(createFirstVersion('pivot'));

        $project = Project::query()
            ->where('name', 'Wellington St.')
            ->first();

        $products = $project->products->pluck('name');

        expect($products)->toContain('Furnace');
        expect($products)->toContain('Fan');
        expect($products)->not()->toContain('Heat Pump');

        $pivot = $project
            ->products()
            ->where('name', 'Furnace')
            ->first()
            ->pivot;

        expect($pivot->quantity)->toBe(2);

        versions()->setActive(releaseAndCreatePatchVersion('pivot'));

        $furnace = Product::query()
            ->where('name', 'Furnace')
            ->first();

        $project->products()->sync([
            $furnace->id => [
                'quantity' => 10,
            ],
        ]);

        $products = $project->activeVersion()->products->pluck('name');

        expect($products)->toContain('Furnace');
        expect($products)->not()->toContain('Fan');
        expect($products)->not()->toContain('Heat Pump');

        $pivot = $project
            ->products()
            ->where('name', 'Furnace')
            ->first()
            ->pivot;

        expect($pivot->quantity)->toBe(10);

        versions()->setActive(versions()->byNumber('1.0.0'));

        $products = $project->activeVersion()->products->pluck('name');

        expect($products)->toContain('Furnace');
        expect($products)->toContain('Fan');
        expect($products)->not()->toContain('Heat Pump');

        $pivot = $project
            ->products()
            ->where('name', 'Furnace')
            ->first()
            ->pivot;

        expect($pivot->quantity)->toBe(2);
    });

    it('can sync without detaching versioned models to versioned models', function () {
        versions()->setActive(createFirstVersion('pivot'));

        $project = Project::query()
            ->where('name', 'Pennsylvania Ave.')
            ->first();

        $products = $project->products->pluck('name');

        expect($products)->toContain('Lightbulb');
        expect($products)->toContain('Outlet');
        expect($products)->not()->toContain('Switch');

        versions()->setActive(releaseAndCreatePatchVersion('pivot'));

        $switch = Product::query()
            ->where('name', 'Switch')
            ->first();

        $project->products()->syncWithoutDetaching([
            $switch->id => [
                'quantity' => 10,
            ],
        ]);

        $products = $project->activeVersion()->products->pluck('name');

        expect($products)->toContain('Lightbulb');
        expect($products)->toContain('Outlet');
        expect($products)->toContain('Switch');

        versions()->setActive(versions()->byNumber('1.0.0'));

        $products = $project->activeVersion()->products->pluck('name');

        expect($products)->toContain('Lightbulb');
        expect($products)->toContain('Outlet');
        expect($products)->not()->toContain('Switch');
    });

    it('can attach versioned models to unversioned models', function () {
        versions()->setActive(createFirstVersion('pivot'));

        $project = Project::query()
            ->where('name', 'Wellington St.')
            ->first();

        $products = $project->categories->pluck('name');

        expect($products)->toContain('Mechanical');
        expect($products)->not()->toContain('Electrical');
        expect($products)->not()->toContain('Plumbing');

        versions()->setActive(releaseAndCreatePatchVersion('pivot'));

        $electrical = Category::query()
            ->where('name', 'Electrical')
            ->first();

        $project->categories()->attach([
            $electrical->id,
        ]);

        $products = $project->activeVersion()->categories->pluck('name');

        expect($products)->toContain('Mechanical');
        expect($products)->toContain('Electrical');
        expect($products)->not()->toContain('Plumbing');

        versions()->setActive(versions()->byNumber('1.0.0'));

        $products = $project->activeVersion()->categories->pluck('name');

        expect($products)->toContain('Mechanical');
        expect($products)->not()->toContain('Electrical');
        expect($products)->not()->toContain('Plumbing');
    });

    it('can detach versioned models to unversioned models', function () {
        versions()->setActive(createFirstVersion('pivot'));

        $project = Project::query()
            ->where('name', 'Pennsylvania Ave.')
            ->first();

        $products = $project->categories->pluck('name');

        expect($products)->not()->toContain('Mechanical');
        expect($products)->toContain('Electrical');
        expect($products)->not()->toContain('Plumbing');

        versions()->setActive(releaseAndCreatePatchVersion('pivot'));

        $project->categories()->detach([
            Category::query()
                ->where('name', 'Electrical')
                ->first()
                ->id,
        ]);

        $products = $project->activeVersion()->categories->pluck('name');

        expect($products)->not()->toContain('Mechanical');
        expect($products)->not()->toContain('Electrical');
        expect($products)->not()->toContain('Plumbing');

        versions()->setActive(versions()->byNumber('1.0.0'));

        $products = $project->activeVersion()->categories->pluck('name');

        expect($products)->not()->toContain('Mechanical');
        expect($products)->toContain('Electrical');
        expect($products)->not()->toContain('Plumbing');
    });

    it('can delete the pivot for versioned models to unversioned models', function () {
        versions()->setActive(createFirstVersion('pivot'));

        $project = Project::query()
            ->where('name', 'Downing St.')
            ->first();

        $products = $project->categories->pluck('name');

        expect($products)->not()->toContain('Mechanical');
        expect($products)->not()->toContain('Electrical');
        expect($products)->toContain('Plumbing');

        versions()->setActive(releaseAndCreatePatchVersion('pivot'));

        $project->categories()->where('name', 'Plumbing')->first()->pivot->delete();

        $products = $project->activeVersion()->categories->pluck('name');

        expect($products)->not()->toContain('Mechanical');
        expect($products)->not()->toContain('Electrical');
        expect($products)->not()->toContain('Plumbing');

        versions()->setActive(versions()->byNumber('1.0.0'));

        $products = $project->activeVersion()->categories->pluck('name');

        expect($products)->not()->toContain('Mechanical');
        expect($products)->not()->toContain('Electrical');
        expect($products)->toContain('Plumbing');
    });

    it('can sync versioned models to unversioned models', function () {
        versions()->setActive(createFirstVersion('pivot'));

        $project = Project::query()
            ->where('name', 'Wellington St.')
            ->first();

        $products = $project->categories->pluck('name');

        expect($products)->toContain('Mechanical');
        expect($products)->not()->toContain('Electrical');
        expect($products)->not()->toContain('Plumbing');

        versions()->setActive(releaseAndCreatePatchVersion('pivot'));

        $electrical = Category::query()
            ->where('name', 'Electrical')
            ->first();

        $project->categories()->sync([
            $electrical->id,
        ]);

        $products = $project->activeVersion()->categories->pluck('name');

        expect($products)->not()->toContain('Mechanical');
        expect($products)->toContain('Electrical');
        expect($products)->not()->toContain('Plumbing');

        versions()->setActive(versions()->byNumber('1.0.0'));

        $products = $project->activeVersion()->categories->pluck('name');

        expect($products)->toContain('Mechanical');
        expect($products)->not()->toContain('Electrical');
        expect($products)->not()->toContain('Plumbing');
    });

    it('can sync without detaching versioned models to unversioned models', function () {
        versions()->setActive(createFirstVersion('pivot'));

        $project = Project::query()
            ->where('name', 'Pennsylvania Ave.')
            ->first();

        $products = $project->categories->pluck('name');

        expect($products)->not()->toContain('Mechanical');
        expect($products)->toContain('Electrical');
        expect($products)->not()->toContain('Plumbing');

        versions()->setActive(releaseAndCreatePatchVersion('pivot'));

        $mechanical = Category::query()
            ->where('name', 'Mechanical')
            ->first();

        $project->categories()->syncWithoutDetaching([
            $mechanical->id,
        ]);

        $products = $project->activeVersion()->categories->pluck('name');

        expect($products)->toContain('Mechanical');
        expect($products)->toContain('Electrical');
        expect($products)->not()->toContain('Plumbing');

        versions()->setActive(versions()->byNumber('1.0.0'));

        $products = $project->activeVersion()->categories->pluck('name');

        expect($products)->not()->toContain('Mechanical');
        expect($products)->toContain('Electrical');
        expect($products)->not()->toContain('Plumbing');
    });

    it('can attach unversioned models to versioned models', function () {
        versions()->setActive(createFirstVersion('pivot'));

        $category = Category::query()
            ->where('name', 'Mechanical')
            ->first();

        $projects = $category->projects->pluck('name');

        expect($projects)->toContain('Wellington St.');
        expect($projects)->not()->toContain('Pennsylvania Ave.');
        expect($projects)->not()->toContain('Downing St.');

        versions()->setActive(releaseAndCreatePatchVersion('pivot'));

        $downing = Project::query()
            ->where('name', 'Downing St.')
            ->first();

        $category->projects()->attach([
            $downing->ulid,
        ]);

        $projects = $category->projects()->get()->pluck('name');

        expect($projects)->toContain('Wellington St.');
        expect($projects)->not()->toContain('Pennsylvania Ave.');
        expect($projects)->toContain('Downing St.');

        versions()->setActive(versions()->byNumber('1.0.0'));

        $projects = $category->unsetRelations()->projects->pluck('name');

        expect($projects)->toContain('Wellington St.');
        expect($projects)->not()->toContain('Pennsylvania Ave.');
        expect($projects)->not()->toContain('Downing St.');
    });

    it('can detach unversioned models to versioned models', function () {
        versions()->setActive(createFirstVersion('pivot'));

        $category = Category::query()
            ->where('name', 'Mechanical')
            ->first();

        $projects = $category->projects->pluck('name');

        expect($projects)->toContain('Wellington St.');
        expect($projects)->not()->toContain('Pennsylvania Ave.');
        expect($projects)->not()->toContain('Downing St.');

        versions()->setActive(releaseAndCreatePatchVersion('pivot'));

        $category->projects()->detach([
            Project::query()
                ->where('name', 'Wellington St.')
                ->first()
                ->ulid,
        ]);

        $projects = $category->projects()->get()->pluck('name');

        expect($projects)->not()->toContain('Wellington St.');
        expect($projects)->not()->toContain('Pennsylvania Ave.');
        expect($projects)->not()->toContain('Downing St.');

        versions()->setActive(versions()->byNumber('1.0.0'));

        $projects = $category->unsetRelations()->projects->pluck('name');

        expect($projects)->toContain('Wellington St.');
        expect($projects)->not()->toContain('Pennsylvania Ave.');
        expect($projects)->not()->toContain('Downing St.');
    });

    it('can delete the pivot for unversioned models to versioned models', function () {
        versions()->setActive(createFirstVersion('pivot'));

        $category = Category::query()
            ->where('name', 'Mechanical')
            ->first();

        $projects = $category->projects->pluck('name');

        expect($projects)->toContain('Wellington St.');
        expect($projects)->not()->toContain('Pennsylvania Ave.');
        expect($projects)->not()->toContain('Downing St.');

        versions()->setActive(releaseAndCreatePatchVersion('pivot'));

        $category->projects()->where('name', 'Wellington St.')->first()->pivot->delete();

        $projects = $category->projects()->get()->pluck('name');

        expect($projects)->not()->toContain('Wellington St.');
        expect($projects)->not()->toContain('Pennsylvania Ave.');
        expect($projects)->not()->toContain('Downing St.');

        versions()->setActive(versions()->byNumber('1.0.0'));

        $projects = $category->unsetRelations()->projects->pluck('name');

        expect($projects)->toContain('Wellington St.');
        expect($projects)->not()->toContain('Pennsylvania Ave.');
        expect($projects)->not()->toContain('Downing St.');
    });

    it('can sync unversioned models to versioned models', function () {
        versions()->setActive(createFirstVersion('pivot'));

        $category = Category::query()
            ->where('name', 'Mechanical')
            ->first();

        $projects = $category->projects->pluck('name');

        expect($projects)->toContain('Wellington St.');
        expect($projects)->not()->toContain('Pennsylvania Ave.');
        expect($projects)->not()->toContain('Downing St.');

        versions()->setActive(releaseAndCreatePatchVersion('pivot'));

        $downing = Project::query()
            ->where('name', 'Downing St.')
            ->first();

        $category->projects()->sync([
            $downing->ulid,
        ]);

        $projects = $category->projects()->get()->pluck('name');

        expect($projects)->not()->toContain('Wellington St.');
        expect($projects)->not()->toContain('Pennsylvania Ave.');
        expect($projects)->toContain('Downing St.');

        versions()->setActive(versions()->byNumber('1.0.0'));

        $projects = $category->unsetRelations()->projects->pluck('name');

        expect($projects)->toContain('Wellington St.');
        expect($projects)->not()->toContain('Pennsylvania Ave.');
        expect($projects)->not()->toContain('Downing St.');
    });

    it('can sync without detaching unversioned models to versioned models', function () {
        versions()->setActive(createFirstVersion('pivot'));

        $category = Category::query()
            ->where('name', 'Mechanical')
            ->first();

        $projects = $category->projects->pluck('name');

        expect($projects)->toContain('Wellington St.');
        expect($projects)->not()->toContain('Pennsylvania Ave.');
        expect($projects)->not()->toContain('Downing St.');

        versions()->setActive(releaseAndCreatePatchVersion('pivot'));

        $downing = Project::query()
            ->where('name', 'Downing St.')
            ->first();

        $category->projects()->syncWithoutDetaching([
            $downing->ulid,
        ]);

        $projects = $category->projects()->get()->pluck('name');

        expect($projects)->toContain('Wellington St.');
        expect($projects)->not()->toContain('Pennsylvania Ave.');
        expect($projects)->toContain('Downing St.');

        versions()->setActive(versions()->byNumber('1.0.0'));

        $projects = $category->unsetRelations()->projects->pluck('name');

        expect($projects)->toContain('Wellington St.');
        expect($projects)->not()->toContain('Pennsylvania Ave.');
        expect($projects)->not()->toContain('Downing St.');
    });
});
