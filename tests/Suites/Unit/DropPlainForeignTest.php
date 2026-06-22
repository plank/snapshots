<?php

use Illuminate\Database\SQLiteConnection;
use Illuminate\Support\Facades\DB;
use Plank\Snapshots\Connection\SnapshotMySqlGrammar;
use Plank\Snapshots\Connection\SnapshotPostgresGrammar;
use Plank\Snapshots\Connection\SnapshotSQLiteGrammar;
use Plank\Snapshots\Migrator\Blueprint\SnapshotBlueprint;
use Plank\Snapshots\Tests\Models\Plain;
use Plank\Snapshots\Tests\Models\PlainAlso;
use Plank\Snapshots\Tests\Models\PlainUlid;
use Plank\Snapshots\Tests\Models\PlainUlidAlso;
use Plank\Snapshots\Tests\Models\PlainUuid;
use Plank\Snapshots\Tests\Models\PlainUuidAlso;

function snapshotConnection(string $prefix = ''): SQLiteConnection
{
    $base = DB::connection('testing');

    $connection = new SQLiteConnection(
        $base->getRawPdo(),
        ':memory:',
        $prefix,
        ['prefix' => $prefix],
    );

    $grammar = new SnapshotSQLiteGrammar($connection);
    $connection->setSchemaGrammar($grammar);

    return $connection;
}

function blueprint(string $table, string $prefix = ''): SnapshotBlueprint
{
    return new SnapshotBlueprint(snapshotConnection($prefix), $table);
}

describe('dropPlainForeign generates correct index names', function () {
    it('generates matching index names for create and drop without prefix', function () {
        $createBlueprint = blueprint('snapshotteds');
        $createBlueprint->plainForeign(['plain_id']);

        $dropBlueprint = blueprint('snapshotteds');
        $dropBlueprint->dropPlainForeign(['plain_id']);

        $createCommand = collect($createBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'plainForeign');
        $dropCommand = collect($dropBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropPlainForeign');

        expect($createCommand)->not->toBeNull();
        expect($dropCommand)->not->toBeNull();
        expect($dropCommand->index)->toBe($createCommand->index);
    });

    it('generates matching index names for create and drop with snapshot prefix', function () {
        $createBlueprint = blueprint('snapshotteds', 'v1_0_0_');
        $createBlueprint->plainForeign(['plain_id']);

        $dropBlueprint = blueprint('snapshotteds', 'v1_0_0_');
        $dropBlueprint->dropPlainForeign(['plain_id']);

        $createCommand = collect($createBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'plainForeign');
        $dropCommand = collect($dropBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropPlainForeign');

        expect($createCommand)->not->toBeNull();
        expect($dropCommand)->not->toBeNull();
        expect($dropCommand->index)->toBe($createCommand->index);
        expect($dropCommand->index)->toContain('v1_0_0_');
        expect($dropCommand->index)->toContain('plainforeign');
    });

    it('generates correct index name format', function () {
        $bp = blueprint('snapshotteds', 'v1_0_0_');
        $bp->dropPlainForeign(['plain_id']);

        $command = collect($bp->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropPlainForeign');

        expect($command->index)->toBe('v1_0_0_snapshotteds_plain_id_plainforeign');
    });

    it('generates correct index name for multiple columns', function () {
        $createBlueprint = blueprint('snapshotteds', 'v1_0_0_');
        $createBlueprint->plainForeign(['first_id', 'second_id']);

        $dropBlueprint = blueprint('snapshotteds', 'v1_0_0_');
        $dropBlueprint->dropPlainForeign(['first_id', 'second_id']);

        $createCommand = collect($createBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'plainForeign');
        $dropCommand = collect($dropBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropPlainForeign');

        expect($dropCommand->index)->toBe($createCommand->index);
        expect($dropCommand->index)->toBe('v1_0_0_snapshotteds_first_id_second_id_plainforeign');
    });

    it('uses explicit index name when dropping by name', function () {
        $bp = blueprint('snapshotteds', 'v1_0_0_');
        $bp->dropPlainForeign('my_custom_index_name');

        $command = collect($bp->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropPlainForeign');

        expect($command->index)->toBe('v1_0_0_my_custom_index_name');
    });
});

describe('dropPlainForeignIdFor resolves model foreign keys', function () {
    it('resolves integer model foreign key', function () {
        $bp = blueprint('snapshotteds', 'v1_0_0_');
        $bp->dropPlainForeignIdFor(Plain::class);

        $command = collect($bp->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropPlainForeign');

        expect($command)->not->toBeNull();
        expect($command->columns)->toBe(['plain_id']);
        expect($command->index)->toBe('v1_0_0_snapshotteds_plain_id_plainforeign');
    });

    it('resolves integer model foreign key with custom column', function () {
        $bp = blueprint('snapshotteds', 'v1_0_0_');
        $bp->dropPlainForeignIdFor(Plain::class, 'custom_fk_id');

        $command = collect($bp->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropPlainForeign');

        expect($command->columns)->toBe(['custom_fk_id']);
    });

    it('resolves uuid model foreign key', function () {
        $bp = blueprint('snapshotted_uuids', 'v1_0_0_');
        $bp->dropPlainForeignIdFor(PlainUuid::class);

        $command = collect($bp->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropPlainForeign');

        expect($command)->not->toBeNull();
        expect($command->columns)->toBe(['plain_uuid_id']);
    });

    it('resolves ulid model foreign key', function () {
        $bp = blueprint('snapshotted_ulids', 'v1_0_0_');
        $bp->dropPlainForeignIdFor(PlainUlid::class);

        $command = collect($bp->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropPlainForeign');

        expect($command)->not->toBeNull();
        expect($command->columns)->toBe(['plain_ulid_id']);
    });

    it('accepts a model instance', function () {
        $bp = blueprint('snapshotteds', 'v1_0_0_');
        $bp->dropPlainForeignIdFor(new Plain);

        $command = collect($bp->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropPlainForeign');

        expect($command)->not->toBeNull();
        expect($command->columns)->toBe(['plain_id']);
    });
});

describe('dropConstrainedPlainForeignId generates both drop FK and drop column commands', function () {
    it('generates both dropPlainForeign and dropColumn commands', function () {
        $bp = blueprint('snapshotteds', 'v1_0_0_');
        $bp->dropConstrainedPlainForeignId('plain_id');

        $commands = collect($bp->getCommands());

        $dropFk = $commands->first(fn ($cmd) => $cmd->name === 'dropPlainForeign');
        $dropColumn = $commands->first(fn ($cmd) => $cmd->name === 'dropColumn');

        expect($dropFk)->not->toBeNull();
        expect($dropFk->index)->toBe('v1_0_0_snapshotteds_plain_id_plainforeign');

        expect($dropColumn)->not->toBeNull();
        expect($dropColumn->columns)->toBe(['plain_id']);
    });
});

describe('dropConstrainedPlainForeignIdFor resolves model and generates both commands', function () {
    it('resolves model and generates both drop FK and drop column commands', function () {
        $bp = blueprint('snapshotteds', 'v1_0_0_');
        $bp->dropConstrainedPlainForeignIdFor(Plain::class);

        $commands = collect($bp->getCommands());

        $dropFk = $commands->first(fn ($cmd) => $cmd->name === 'dropPlainForeign');
        $dropColumn = $commands->first(fn ($cmd) => $cmd->name === 'dropColumn');

        expect($dropFk)->not->toBeNull();
        expect($dropFk->index)->toBe('v1_0_0_snapshotteds_plain_id_plainforeign');
        expect($dropFk->columns)->toBe(['plain_id']);

        expect($dropColumn)->not->toBeNull();
        expect($dropColumn->columns)->toBe(['plain_id']);
    });

    it('resolves model with custom column', function () {
        $bp = blueprint('snapshotteds', 'v1_0_0_');
        $bp->dropConstrainedPlainForeignIdFor(Plain::class, 'custom_fk');

        $commands = collect($bp->getCommands());

        $dropFk = $commands->first(fn ($cmd) => $cmd->name === 'dropPlainForeign');
        $dropColumn = $commands->first(fn ($cmd) => $cmd->name === 'dropColumn');

        expect($dropFk->columns)->toBe(['custom_fk']);
        expect($dropColumn->columns)->toBe(['custom_fk']);
    });

    it('accepts a model instance', function () {
        $bp = blueprint('snapshotteds', 'v1_0_0_');
        $bp->dropConstrainedPlainForeignIdFor(new PlainAlso);

        $commands = collect($bp->getCommands());

        $dropFk = $commands->first(fn ($cmd) => $cmd->name === 'dropPlainForeign');
        $dropColumn = $commands->first(fn ($cmd) => $cmd->name === 'dropColumn');

        expect($dropFk->columns)->toBe(['plain_also_id']);
        expect($dropColumn->columns)->toBe(['plain_also_id']);
    });

    it('resolves uuid model correctly', function () {
        $bp = blueprint('snapshotted_uuids', 'v1_0_0_');
        $bp->dropConstrainedPlainForeignIdFor(PlainUuidAlso::class);

        $commands = collect($bp->getCommands());

        $dropFk = $commands->first(fn ($cmd) => $cmd->name === 'dropPlainForeign');
        $dropColumn = $commands->first(fn ($cmd) => $cmd->name === 'dropColumn');

        expect($dropFk->columns)->toBe(['plain_uuid_also_id']);
        expect($dropColumn->columns)->toBe(['plain_uuid_also_id']);
    });

    it('resolves ulid model correctly', function () {
        $bp = blueprint('snapshotted_ulids', 'v1_0_0_');
        $bp->dropConstrainedPlainForeignIdFor(PlainUlidAlso::class);

        $commands = collect($bp->getCommands());

        $dropFk = $commands->first(fn ($cmd) => $cmd->name === 'dropPlainForeign');
        $dropColumn = $commands->first(fn ($cmd) => $cmd->name === 'dropColumn');

        expect($dropFk->columns)->toBe(['plain_ulid_also_id']);
        expect($dropColumn->columns)->toBe(['plain_ulid_also_id']);
    });
});

describe('drop index names match create index names across all FK types', function () {
    it('matches for plainForeignId create and drop', function () {
        $createBlueprint = blueprint('snapshotteds', 'v1_0_0_');
        $createBlueprint->plainForeignId('plain_id')
            ->references('id')
            ->on('plains');

        $dropBlueprint = blueprint('snapshotteds', 'v1_0_0_');
        $dropBlueprint->dropPlainForeign(['plain_id']);

        $createCommand = collect($createBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'plainForeign');
        $dropCommand = collect($dropBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropPlainForeign');

        expect($createCommand->index)->toBe($dropCommand->index);
    });

    it('matches for plainForeignUuid create and drop', function () {
        $createBlueprint = blueprint('snapshotted_uuids', 'v1_0_0_');
        $createBlueprint->plainForeignUuid('plain_uuid_id')
            ->references('id')
            ->on('plain_uuids');

        $dropBlueprint = blueprint('snapshotted_uuids', 'v1_0_0_');
        $dropBlueprint->dropPlainForeign(['plain_uuid_id']);

        $createCommand = collect($createBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'plainForeign');
        $dropCommand = collect($dropBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropPlainForeign');

        expect($createCommand->index)->toBe($dropCommand->index);
    });

    it('matches for plainForeignUlid create and drop', function () {
        $createBlueprint = blueprint('snapshotted_ulids', 'v1_0_0_');
        $createBlueprint->plainForeignUlid('plain_ulid_id')
            ->references('id')
            ->on('plain_ulids');

        $dropBlueprint = blueprint('snapshotted_ulids', 'v1_0_0_');
        $dropBlueprint->dropPlainForeign(['plain_ulid_id']);

        $createCommand = collect($createBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'plainForeign');
        $dropCommand = collect($dropBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropPlainForeign');

        expect($createCommand->index)->toBe($dropCommand->index);
    });

    it('matches for plainForeignIdFor create and dropPlainForeignIdFor drop', function () {
        $createBlueprint = blueprint('snapshotteds', 'v1_0_0_');
        $createBlueprint->plainForeignIdFor(Plain::class);

        $dropBlueprint = blueprint('snapshotteds', 'v1_0_0_');
        $dropBlueprint->dropPlainForeignIdFor(Plain::class);

        $createCommand = collect($createBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'plainForeign');
        $dropCommand = collect($dropBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropPlainForeign');

        expect($createCommand->index)->toBe($dropCommand->index);
    });

    it('matches for plainForeignIdFor uuid create and dropPlainForeignIdFor drop', function () {
        $createBlueprint = blueprint('snapshotted_uuids', 'v1_0_0_');
        $createBlueprint->plainForeignIdFor(PlainUuid::class);

        $dropBlueprint = blueprint('snapshotted_uuids', 'v1_0_0_');
        $dropBlueprint->dropPlainForeignIdFor(PlainUuid::class);

        $createCommand = collect($createBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'plainForeign');
        $dropCommand = collect($dropBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropPlainForeign');

        expect($createCommand->index)->toBe($dropCommand->index);
    });

    it('matches for plainForeignIdFor ulid create and dropPlainForeignIdFor drop', function () {
        $createBlueprint = blueprint('snapshotted_ulids', 'v1_0_0_');
        $createBlueprint->plainForeignIdFor(PlainUlid::class);

        $dropBlueprint = blueprint('snapshotted_ulids', 'v1_0_0_');
        $dropBlueprint->dropPlainForeignIdFor(PlainUlid::class);

        $createCommand = collect($createBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'plainForeign');
        $dropCommand = collect($dropBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropPlainForeign');

        expect($createCommand->index)->toBe($dropCommand->index);
    });
});

describe('compile methods produce correct SQL', function () {
    it('compileDropPlainForeign produces correct MySQL SQL', function () {
        $connection = snapshotConnection('v1_0_0_');
        $grammar = new SnapshotMySqlGrammar($connection);

        $bp = blueprint('snapshotteds', 'v1_0_0_');
        $bp->dropPlainForeign(['plain_id']);

        $command = collect($bp->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropPlainForeign');

        $sql = $grammar->compileDropPlainForeign($bp, $command);

        expect($sql)->toContain('alter table');
        expect($sql)->toContain('v1_0_0_snapshotteds');
        expect($sql)->toContain('drop foreign key');
        expect($sql)->toContain('v1_0_0_snapshotteds_plain_id_plainforeign');
    });

    it('compileDropPlainForeign produces correct Postgres SQL', function () {
        $connection = snapshotConnection('v1_0_0_');
        $grammar = new SnapshotPostgresGrammar($connection);

        $bp = blueprint('snapshotteds', 'v1_0_0_');
        $bp->dropPlainForeign(['plain_id']);

        $command = collect($bp->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropPlainForeign');

        $sql = $grammar->compileDropPlainForeign($bp, $command);

        expect($sql)->toContain('alter table');
        expect($sql)->toContain('v1_0_0_snapshotteds');
        expect($sql)->toContain('drop constraint');
        expect($sql)->toContain('v1_0_0_snapshotteds_plain_id_plainforeign');
    });
});
