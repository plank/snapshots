<?php

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use function Pest\Laravel\artisan;
use Plank\Snapshots\Tests\Models\Document;

describe('SnapshotBlueprint uses versions for named indexes', function () {
    beforeEach(function () {
        artisan('migrate', [
            '--path' => migrationPath('schema/create'),
            '--realpath' => true,
        ])->run();
    });

    it('can drop named indexes from tables', function () {
        $tableName = (new Document())->getTable();
        $indexes = Collection::wrap(DB::select("PRAGMA index_list('$tableName')"));

        expect($indexes)->toHaveCount(2);
        expect($indexes->pluck('name'))->toContain('idx_title');

        artisan('migrate', [
            '--path' => migrationPath('schema/drop_named_index'),
            '--realpath' => true,
        ])->run();

        $indexes = Collection::wrap(DB::select("PRAGMA index_list('$tableName')"));
        expect($indexes)->toHaveCount(1);

        versions()->setActive(createFirstVersion('schema/create'));

        $tableName = (new Document())->getTable();
        $indexes = Collection::wrap(DB::select("PRAGMA index_list('$tableName')"));

        expect($indexes)->toHaveCount(2);
        expect($indexes->pluck('name'))->toContain('v1_0_0_idx_title');

        artisan('migrate', [
            '--path' => migrationPath('schema/drop_named_index'),
            '--realpath' => true,
        ])->run();

        $indexes = Collection::wrap(DB::select("PRAGMA index_list('$tableName')"));
        expect($indexes)->toHaveCount(1);
    });
});

describe('SnapshotBlueprint uses versions for computed indexes', function () {
    beforeEach(function () {
        artisan('migrate', [
            '--path' => migrationPath('schema/create'),
            '--realpath' => true,
        ])->run();
    });

    it('can drop computed indexes from tables', function () {
        $tableName = (new Document())->getTable();
        $indexes = Collection::wrap(DB::select("PRAGMA index_list('$tableName')"));
        expect($indexes)->toHaveCount(2);
        expect($indexes->pluck('name'))->toContain($tableName.'_released_at_index');

        artisan('migrate', [
            '--path' => migrationPath('schema/drop_computed_index'),
            '--realpath' => true,
        ])->run();

        $indexes = Collection::wrap(DB::select("PRAGMA index_list('$tableName')"));
        expect($indexes)->toHaveCount(1);

        versions()->setActive(createFirstVersion('schema/create'));

        $tableName = (new Document())->getTable();
        $indexes = Collection::wrap(DB::select("PRAGMA index_list('$tableName')"));

        expect($indexes)->toHaveCount(2);
        expect($indexes->pluck('name'))->toContain($tableName.'_released_at_index');

        artisan('migrate', [
            '--path' => migrationPath('schema/drop_computed_index'),
            '--realpath' => true,
        ])->run();

        $indexes = Collection::wrap(DB::select("PRAGMA index_list('$tableName')"));
        expect($indexes)->toHaveCount(1);
    });
});
