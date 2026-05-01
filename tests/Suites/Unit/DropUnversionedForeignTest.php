<?php

use Illuminate\Database\SQLiteConnection;
use Illuminate\Support\Facades\DB;
use Plank\Snapshots\Connection\SnapshotSQLiteGrammar;
use Plank\Snapshots\Migrator\Blueprint\SnapshotBlueprint;
use Plank\Snapshots\Tests\Models\Unversioned;
use Plank\Snapshots\Tests\Models\UnversionedAlso;
use Plank\Snapshots\Tests\Models\UnversionedUlid;
use Plank\Snapshots\Tests\Models\UnversionedUlidAlso;
use Plank\Snapshots\Tests\Models\UnversionedUuid;
use Plank\Snapshots\Tests\Models\UnversionedUuidAlso;

function snapshotConnection(string $prefix = ''): SQLiteConnection
{
    $base = DB::connection('testing');

    $connection = new SQLiteConnection(
        $base->getRawPdo(),
        ':memory:',
        $prefix,
        ['prefix' => $prefix, 'prefix_indexes' => true],
    );

    $grammar = new SnapshotSQLiteGrammar($connection);
    $connection->setSchemaGrammar($grammar);

    return $connection;
}

function blueprint(string $table, string $prefix = ''): SnapshotBlueprint
{
    return new SnapshotBlueprint(snapshotConnection($prefix), $table);
}

describe('dropUnversionedForeign generates correct index names', function () {
    it('generates matching index names for create and drop without prefix', function () {
        $createBlueprint = blueprint('versioneds');
        $createBlueprint->unversionedForeign(['unversioned_id']);

        $dropBlueprint = blueprint('versioneds');
        $dropBlueprint->dropUnversionedForeign(['unversioned_id']);

        $createCommand = collect($createBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'unversionedForeign');
        $dropCommand = collect($dropBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');

        expect($createCommand)->not->toBeNull();
        expect($dropCommand)->not->toBeNull();
        expect($dropCommand->index)->toBe($createCommand->index);
    });

    it('generates matching index names for create and drop with version prefix', function () {
        $createBlueprint = blueprint('versioneds', 'v1_0_0_');
        $createBlueprint->unversionedForeign(['unversioned_id']);

        $dropBlueprint = blueprint('versioneds', 'v1_0_0_');
        $dropBlueprint->dropUnversionedForeign(['unversioned_id']);

        $createCommand = collect($createBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'unversionedForeign');
        $dropCommand = collect($dropBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');

        expect($createCommand)->not->toBeNull();
        expect($dropCommand)->not->toBeNull();
        expect($dropCommand->index)->toBe($createCommand->index);
        expect($dropCommand->index)->toContain('v1_0_0_');
        expect($dropCommand->index)->toContain('unversionedforeign');
    });

    it('generates correct index name format', function () {
        $bp = blueprint('versioneds', 'v1_0_0_');
        $bp->dropUnversionedForeign(['unversioned_id']);

        $command = collect($bp->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');

        expect($command->index)->toBe('v1_0_0_versioneds_unversioned_id_unversionedforeign');
    });

    it('generates correct index name for multiple columns', function () {
        $createBlueprint = blueprint('versioneds', 'v1_0_0_');
        $createBlueprint->unversionedForeign(['first_id', 'second_id']);

        $dropBlueprint = blueprint('versioneds', 'v1_0_0_');
        $dropBlueprint->dropUnversionedForeign(['first_id', 'second_id']);

        $createCommand = collect($createBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'unversionedForeign');
        $dropCommand = collect($dropBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');

        expect($dropCommand->index)->toBe($createCommand->index);
        expect($dropCommand->index)->toBe('v1_0_0_versioneds_first_id_second_id_unversionedforeign');
    });

    it('uses explicit index name when dropping by name', function () {
        $bp = blueprint('versioneds', 'v1_0_0_');
        $bp->dropUnversionedForeign('my_custom_index_name');

        $command = collect($bp->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');

        expect($command->index)->toBe('v1_0_0_my_custom_index_name');
    });
});

describe('dropUnversionedForeignIdFor resolves model foreign keys', function () {
    it('resolves integer model foreign key', function () {
        $bp = blueprint('versioneds', 'v1_0_0_');
        $bp->dropUnversionedForeignIdFor(Unversioned::class);

        $command = collect($bp->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');

        expect($command)->not->toBeNull();
        expect($command->columns)->toBe(['unversioned_id']);
        expect($command->index)->toBe('v1_0_0_versioneds_unversioned_id_unversionedforeign');
    });

    it('resolves integer model foreign key with custom column', function () {
        $bp = blueprint('versioneds', 'v1_0_0_');
        $bp->dropUnversionedForeignIdFor(Unversioned::class, 'custom_fk_id');

        $command = collect($bp->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');

        expect($command->columns)->toBe(['custom_fk_id']);
    });

    it('resolves uuid model foreign key', function () {
        $bp = blueprint('versioned_uuids', 'v1_0_0_');
        $bp->dropUnversionedForeignIdFor(UnversionedUuid::class);

        $command = collect($bp->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');

        expect($command)->not->toBeNull();
        expect($command->columns)->toBe(['unversioned_uuid_id']);
    });

    it('resolves ulid model foreign key', function () {
        $bp = blueprint('versioned_ulids', 'v1_0_0_');
        $bp->dropUnversionedForeignIdFor(UnversionedUlid::class);

        $command = collect($bp->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');

        expect($command)->not->toBeNull();
        expect($command->columns)->toBe(['unversioned_ulid_id']);
    });

    it('accepts a model instance', function () {
        $bp = blueprint('versioneds', 'v1_0_0_');
        $bp->dropUnversionedForeignIdFor(new Unversioned);

        $command = collect($bp->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');

        expect($command)->not->toBeNull();
        expect($command->columns)->toBe(['unversioned_id']);
    });
});

describe('dropConstrainedUnversionedForeignId generates both drop FK and drop column commands', function () {
    it('generates both dropUnversionedForeign and dropColumn commands', function () {
        $bp = blueprint('versioneds', 'v1_0_0_');
        $bp->dropConstrainedUnversionedForeignId('unversioned_id');

        $commands = collect($bp->getCommands());

        $dropFk = $commands->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');
        $dropColumn = $commands->first(fn ($cmd) => $cmd->name === 'dropColumn');

        expect($dropFk)->not->toBeNull();
        expect($dropFk->index)->toBe('v1_0_0_versioneds_unversioned_id_unversionedforeign');

        expect($dropColumn)->not->toBeNull();
        expect($dropColumn->columns)->toBe(['unversioned_id']);
    });
});

describe('dropConstrainedUnversionedForeignIdFor resolves model and generates both commands', function () {
    it('resolves model and generates both drop FK and drop column commands', function () {
        $bp = blueprint('versioneds', 'v1_0_0_');
        $bp->dropConstrainedUnversionedForeignIdFor(Unversioned::class);

        $commands = collect($bp->getCommands());

        $dropFk = $commands->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');
        $dropColumn = $commands->first(fn ($cmd) => $cmd->name === 'dropColumn');

        expect($dropFk)->not->toBeNull();
        expect($dropFk->index)->toBe('v1_0_0_versioneds_unversioned_id_unversionedforeign');
        expect($dropFk->columns)->toBe(['unversioned_id']);

        expect($dropColumn)->not->toBeNull();
        expect($dropColumn->columns)->toBe(['unversioned_id']);
    });

    it('resolves model with custom column', function () {
        $bp = blueprint('versioneds', 'v1_0_0_');
        $bp->dropConstrainedUnversionedForeignIdFor(Unversioned::class, 'custom_fk');

        $commands = collect($bp->getCommands());

        $dropFk = $commands->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');
        $dropColumn = $commands->first(fn ($cmd) => $cmd->name === 'dropColumn');

        expect($dropFk->columns)->toBe(['custom_fk']);
        expect($dropColumn->columns)->toBe(['custom_fk']);
    });

    it('accepts a model instance', function () {
        $bp = blueprint('versioneds', 'v1_0_0_');
        $bp->dropConstrainedUnversionedForeignIdFor(new UnversionedAlso);

        $commands = collect($bp->getCommands());

        $dropFk = $commands->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');
        $dropColumn = $commands->first(fn ($cmd) => $cmd->name === 'dropColumn');

        expect($dropFk->columns)->toBe(['unversioned_also_id']);
        expect($dropColumn->columns)->toBe(['unversioned_also_id']);
    });

    it('resolves uuid model correctly', function () {
        $bp = blueprint('versioned_uuids', 'v1_0_0_');
        $bp->dropConstrainedUnversionedForeignIdFor(UnversionedUuidAlso::class);

        $commands = collect($bp->getCommands());

        $dropFk = $commands->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');
        $dropColumn = $commands->first(fn ($cmd) => $cmd->name === 'dropColumn');

        expect($dropFk->columns)->toBe(['unversioned_uuid_also_id']);
        expect($dropColumn->columns)->toBe(['unversioned_uuid_also_id']);
    });

    it('resolves ulid model correctly', function () {
        $bp = blueprint('versioned_ulids', 'v1_0_0_');
        $bp->dropConstrainedUnversionedForeignIdFor(UnversionedUlidAlso::class);

        $commands = collect($bp->getCommands());

        $dropFk = $commands->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');
        $dropColumn = $commands->first(fn ($cmd) => $cmd->name === 'dropColumn');

        expect($dropFk->columns)->toBe(['unversioned_ulid_also_id']);
        expect($dropColumn->columns)->toBe(['unversioned_ulid_also_id']);
    });
});

describe('drop index names match create index names across all FK types', function () {
    it('matches for unversionedForeignId create and drop', function () {
        $createBlueprint = blueprint('versioneds', 'v1_0_0_');
        $createBlueprint->unversionedForeignId('unversioned_id')
            ->references('id')
            ->on('unversioneds');

        $dropBlueprint = blueprint('versioneds', 'v1_0_0_');
        $dropBlueprint->dropUnversionedForeign(['unversioned_id']);

        $createCommand = collect($createBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'unversionedForeign');
        $dropCommand = collect($dropBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');

        expect($createCommand->index)->toBe($dropCommand->index);
    });

    it('matches for unversionedForeignUuid create and drop', function () {
        $createBlueprint = blueprint('versioned_uuids', 'v1_0_0_');
        $createBlueprint->unversionedForeignUuid('unversioned_uuid_id')
            ->references('id')
            ->on('unversioned_uuids');

        $dropBlueprint = blueprint('versioned_uuids', 'v1_0_0_');
        $dropBlueprint->dropUnversionedForeign(['unversioned_uuid_id']);

        $createCommand = collect($createBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'unversionedForeign');
        $dropCommand = collect($dropBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');

        expect($createCommand->index)->toBe($dropCommand->index);
    });

    it('matches for unversionedForeignUlid create and drop', function () {
        $createBlueprint = blueprint('versioned_ulids', 'v1_0_0_');
        $createBlueprint->unversionedForeignUlid('unversioned_ulid_id')
            ->references('id')
            ->on('unversioned_ulids');

        $dropBlueprint = blueprint('versioned_ulids', 'v1_0_0_');
        $dropBlueprint->dropUnversionedForeign(['unversioned_ulid_id']);

        $createCommand = collect($createBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'unversionedForeign');
        $dropCommand = collect($dropBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');

        expect($createCommand->index)->toBe($dropCommand->index);
    });

    it('matches for unversionedForeignIdFor create and dropUnversionedForeignIdFor drop', function () {
        $createBlueprint = blueprint('versioneds', 'v1_0_0_');
        $createBlueprint->unversionedForeignIdFor(Unversioned::class);

        $dropBlueprint = blueprint('versioneds', 'v1_0_0_');
        $dropBlueprint->dropUnversionedForeignIdFor(Unversioned::class);

        $createCommand = collect($createBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'unversionedForeign');
        $dropCommand = collect($dropBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');

        expect($createCommand->index)->toBe($dropCommand->index);
    });

    it('matches for unversionedForeignIdFor uuid create and dropUnversionedForeignIdFor drop', function () {
        $createBlueprint = blueprint('versioned_uuids', 'v1_0_0_');
        $createBlueprint->unversionedForeignIdFor(UnversionedUuid::class);

        $dropBlueprint = blueprint('versioned_uuids', 'v1_0_0_');
        $dropBlueprint->dropUnversionedForeignIdFor(UnversionedUuid::class);

        $createCommand = collect($createBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'unversionedForeign');
        $dropCommand = collect($dropBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');

        expect($createCommand->index)->toBe($dropCommand->index);
    });

    it('matches for unversionedForeignIdFor ulid create and dropUnversionedForeignIdFor drop', function () {
        $createBlueprint = blueprint('versioned_ulids', 'v1_0_0_');
        $createBlueprint->unversionedForeignIdFor(UnversionedUlid::class);

        $dropBlueprint = blueprint('versioned_ulids', 'v1_0_0_');
        $dropBlueprint->dropUnversionedForeignIdFor(UnversionedUlid::class);

        $createCommand = collect($createBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'unversionedForeign');
        $dropCommand = collect($dropBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');

        expect($createCommand->index)->toBe($dropCommand->index);
    });
});

describe('compile methods produce correct SQL', function () {
    it('compileDropUnversionedForeign produces correct MySQL SQL', function () {
        $connection = snapshotConnection('v1_0_0_');
        $grammar = new \Plank\Snapshots\Connection\SnapshotMySqlGrammar($connection);

        $bp = blueprint('versioneds', 'v1_0_0_');
        $bp->dropUnversionedForeign(['unversioned_id']);

        $command = collect($bp->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');

        $sql = $grammar->compileDropUnversionedForeign($bp, $command);

        expect($sql)->toContain('alter table');
        expect($sql)->toContain('v1_0_0_versioneds');
        expect($sql)->toContain('drop foreign key');
        expect($sql)->toContain('v1_0_0_versioneds_unversioned_id_unversionedforeign');
    });

    it('compileDropUnversionedForeign produces correct Postgres SQL', function () {
        $connection = snapshotConnection('v1_0_0_');
        $grammar = new \Plank\Snapshots\Connection\SnapshotPostgresGrammar($connection);

        $bp = blueprint('versioneds', 'v1_0_0_');
        $bp->dropUnversionedForeign(['unversioned_id']);

        $command = collect($bp->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');

        $sql = $grammar->compileDropUnversionedForeign($bp, $command);

        expect($sql)->toContain('alter table');
        expect($sql)->toContain('v1_0_0_versioneds');
        expect($sql)->toContain('drop constraint');
        expect($sql)->toContain('v1_0_0_versioneds_unversioned_id_unversionedforeign');
    });
});
