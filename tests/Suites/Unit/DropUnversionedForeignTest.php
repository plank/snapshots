<?php

use Plank\Snapshots\Migrator\Blueprint\SnapshotBlueprint;
use Plank\Snapshots\Tests\Models\Unversioned;
use Plank\Snapshots\Tests\Models\UnversionedAlso;
use Plank\Snapshots\Tests\Models\UnversionedUlid;
use Plank\Snapshots\Tests\Models\UnversionedUlidAlso;
use Plank\Snapshots\Tests\Models\UnversionedUuid;
use Plank\Snapshots\Tests\Models\UnversionedUuidAlso;

describe('dropUnversionedForeign generates correct index names', function () {
    it('generates matching index names for create and drop without prefix', function () {
        $createBlueprint = new SnapshotBlueprint('versioneds');
        $createBlueprint->unversionedForeign(['unversioned_id']);

        $dropBlueprint = new SnapshotBlueprint('versioneds');
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
        $createBlueprint = new SnapshotBlueprint('versioneds', null, 'v1_0_0_');
        $createBlueprint->unversionedForeign(['unversioned_id']);

        $dropBlueprint = new SnapshotBlueprint('versioneds', null, 'v1_0_0_');
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
        $blueprint = new SnapshotBlueprint('versioneds', null, 'v1_0_0_');
        $blueprint->dropUnversionedForeign(['unversioned_id']);

        $command = collect($blueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');

        expect($command->index)->toBe('v1_0_0_versioneds_unversioned_id_unversionedforeign');
    });

    it('generates correct index name for multiple columns', function () {
        $createBlueprint = new SnapshotBlueprint('versioneds', null, 'v1_0_0_');
        $createBlueprint->unversionedForeign(['first_id', 'second_id']);

        $dropBlueprint = new SnapshotBlueprint('versioneds', null, 'v1_0_0_');
        $dropBlueprint->dropUnversionedForeign(['first_id', 'second_id']);

        $createCommand = collect($createBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'unversionedForeign');
        $dropCommand = collect($dropBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');

        expect($dropCommand->index)->toBe($createCommand->index);
        expect($dropCommand->index)->toBe('v1_0_0_versioneds_first_id_second_id_unversionedforeign');
    });

    it('uses explicit index name when dropping by name', function () {
        $blueprint = new SnapshotBlueprint('versioneds', null, 'v1_0_0_');
        $blueprint->dropUnversionedForeign('my_custom_index_name');

        $command = collect($blueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');

        expect($command->index)->toBe('v1_0_0_my_custom_index_name');
    });
});

describe('dropUnversionedForeignIdFor resolves model foreign keys', function () {
    it('resolves integer model foreign key', function () {
        $blueprint = new SnapshotBlueprint('versioneds', null, 'v1_0_0_');
        $blueprint->dropUnversionedForeignIdFor(Unversioned::class);

        $command = collect($blueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');

        expect($command)->not->toBeNull();
        expect($command->columns)->toBe(['unversioned_id']);
        expect($command->index)->toBe('v1_0_0_versioneds_unversioned_id_unversionedforeign');
    });

    it('resolves integer model foreign key with custom column', function () {
        $blueprint = new SnapshotBlueprint('versioneds', null, 'v1_0_0_');
        $blueprint->dropUnversionedForeignIdFor(Unversioned::class, 'custom_fk_id');

        $command = collect($blueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');

        expect($command->columns)->toBe(['custom_fk_id']);
    });

    it('resolves uuid model foreign key', function () {
        $blueprint = new SnapshotBlueprint('versioned_uuids', null, 'v1_0_0_');
        $blueprint->dropUnversionedForeignIdFor(UnversionedUuid::class);

        $command = collect($blueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');

        expect($command)->not->toBeNull();
        expect($command->columns)->toBe(['unversioned_uuid_id']);
    });

    it('resolves ulid model foreign key', function () {
        $blueprint = new SnapshotBlueprint('versioned_ulids', null, 'v1_0_0_');
        $blueprint->dropUnversionedForeignIdFor(UnversionedUlid::class);

        $command = collect($blueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');

        expect($command)->not->toBeNull();
        expect($command->columns)->toBe(['unversioned_ulid_id']);
    });

    it('accepts a model instance', function () {
        $blueprint = new SnapshotBlueprint('versioneds', null, 'v1_0_0_');
        $blueprint->dropUnversionedForeignIdFor(new Unversioned);

        $command = collect($blueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');

        expect($command)->not->toBeNull();
        expect($command->columns)->toBe(['unversioned_id']);
    });
});

describe('dropConstrainedUnversionedForeignId generates both drop FK and drop column commands', function () {
    it('generates both dropUnversionedForeign and dropColumn commands', function () {
        $blueprint = new SnapshotBlueprint('versioneds', null, 'v1_0_0_');
        $blueprint->dropConstrainedUnversionedForeignId('unversioned_id');

        $commands = collect($blueprint->getCommands());

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
        $blueprint = new SnapshotBlueprint('versioneds', null, 'v1_0_0_');
        $blueprint->dropConstrainedUnversionedForeignIdFor(Unversioned::class);

        $commands = collect($blueprint->getCommands());

        $dropFk = $commands->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');
        $dropColumn = $commands->first(fn ($cmd) => $cmd->name === 'dropColumn');

        expect($dropFk)->not->toBeNull();
        expect($dropFk->index)->toBe('v1_0_0_versioneds_unversioned_id_unversionedforeign');
        expect($dropFk->columns)->toBe(['unversioned_id']);

        expect($dropColumn)->not->toBeNull();
        expect($dropColumn->columns)->toBe(['unversioned_id']);
    });

    it('resolves model with custom column', function () {
        $blueprint = new SnapshotBlueprint('versioneds', null, 'v1_0_0_');
        $blueprint->dropConstrainedUnversionedForeignIdFor(Unversioned::class, 'custom_fk');

        $commands = collect($blueprint->getCommands());

        $dropFk = $commands->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');
        $dropColumn = $commands->first(fn ($cmd) => $cmd->name === 'dropColumn');

        expect($dropFk->columns)->toBe(['custom_fk']);
        expect($dropColumn->columns)->toBe(['custom_fk']);
    });

    it('accepts a model instance', function () {
        $blueprint = new SnapshotBlueprint('versioneds', null, 'v1_0_0_');
        $blueprint->dropConstrainedUnversionedForeignIdFor(new UnversionedAlso);

        $commands = collect($blueprint->getCommands());

        $dropFk = $commands->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');
        $dropColumn = $commands->first(fn ($cmd) => $cmd->name === 'dropColumn');

        expect($dropFk->columns)->toBe(['unversioned_also_id']);
        expect($dropColumn->columns)->toBe(['unversioned_also_id']);
    });

    it('resolves uuid model correctly', function () {
        $blueprint = new SnapshotBlueprint('versioned_uuids', null, 'v1_0_0_');
        $blueprint->dropConstrainedUnversionedForeignIdFor(UnversionedUuidAlso::class);

        $commands = collect($blueprint->getCommands());

        $dropFk = $commands->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');
        $dropColumn = $commands->first(fn ($cmd) => $cmd->name === 'dropColumn');

        expect($dropFk->columns)->toBe(['unversioned_uuid_also_id']);
        expect($dropColumn->columns)->toBe(['unversioned_uuid_also_id']);
    });

    it('resolves ulid model correctly', function () {
        $blueprint = new SnapshotBlueprint('versioned_ulids', null, 'v1_0_0_');
        $blueprint->dropConstrainedUnversionedForeignIdFor(UnversionedUlidAlso::class);

        $commands = collect($blueprint->getCommands());

        $dropFk = $commands->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');
        $dropColumn = $commands->first(fn ($cmd) => $cmd->name === 'dropColumn');

        expect($dropFk->columns)->toBe(['unversioned_ulid_also_id']);
        expect($dropColumn->columns)->toBe(['unversioned_ulid_also_id']);
    });
});

describe('drop index names match create index names across all FK types', function () {
    it('matches for unversionedForeignId create and drop', function () {
        $createBlueprint = new SnapshotBlueprint('versioneds', null, 'v1_0_0_');
        $createBlueprint->unversionedForeignId('unversioned_id')
            ->references('id')
            ->on('unversioneds');

        $dropBlueprint = new SnapshotBlueprint('versioneds', null, 'v1_0_0_');
        $dropBlueprint->dropUnversionedForeign(['unversioned_id']);

        $createCommand = collect($createBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'unversionedForeign');
        $dropCommand = collect($dropBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');

        expect($createCommand->index)->toBe($dropCommand->index);
    });

    it('matches for unversionedForeignUuid create and drop', function () {
        $createBlueprint = new SnapshotBlueprint('versioned_uuids', null, 'v1_0_0_');
        $createBlueprint->unversionedForeignUuid('unversioned_uuid_id')
            ->references('id')
            ->on('unversioned_uuids');

        $dropBlueprint = new SnapshotBlueprint('versioned_uuids', null, 'v1_0_0_');
        $dropBlueprint->dropUnversionedForeign(['unversioned_uuid_id']);

        $createCommand = collect($createBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'unversionedForeign');
        $dropCommand = collect($dropBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');

        expect($createCommand->index)->toBe($dropCommand->index);
    });

    it('matches for unversionedForeignUlid create and drop', function () {
        $createBlueprint = new SnapshotBlueprint('versioned_ulids', null, 'v1_0_0_');
        $createBlueprint->unversionedForeignUlid('unversioned_ulid_id')
            ->references('id')
            ->on('unversioned_ulids');

        $dropBlueprint = new SnapshotBlueprint('versioned_ulids', null, 'v1_0_0_');
        $dropBlueprint->dropUnversionedForeign(['unversioned_ulid_id']);

        $createCommand = collect($createBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'unversionedForeign');
        $dropCommand = collect($dropBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');

        expect($createCommand->index)->toBe($dropCommand->index);
    });

    it('matches for unversionedForeignIdFor create and dropUnversionedForeignIdFor drop', function () {
        $createBlueprint = new SnapshotBlueprint('versioneds', null, 'v1_0_0_');
        $createBlueprint->unversionedForeignIdFor(Unversioned::class);

        $dropBlueprint = new SnapshotBlueprint('versioneds', null, 'v1_0_0_');
        $dropBlueprint->dropUnversionedForeignIdFor(Unversioned::class);

        $createCommand = collect($createBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'unversionedForeign');
        $dropCommand = collect($dropBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');

        expect($createCommand->index)->toBe($dropCommand->index);
    });

    it('matches for unversionedForeignIdFor uuid create and dropUnversionedForeignIdFor drop', function () {
        $createBlueprint = new SnapshotBlueprint('versioned_uuids', null, 'v1_0_0_');
        $createBlueprint->unversionedForeignIdFor(UnversionedUuid::class);

        $dropBlueprint = new SnapshotBlueprint('versioned_uuids', null, 'v1_0_0_');
        $dropBlueprint->dropUnversionedForeignIdFor(UnversionedUuid::class);

        $createCommand = collect($createBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'unversionedForeign');
        $dropCommand = collect($dropBlueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');

        expect($createCommand->index)->toBe($dropCommand->index);
    });

    it('matches for unversionedForeignIdFor ulid create and dropUnversionedForeignIdFor drop', function () {
        $createBlueprint = new SnapshotBlueprint('versioned_ulids', null, 'v1_0_0_');
        $createBlueprint->unversionedForeignIdFor(UnversionedUlid::class);

        $dropBlueprint = new SnapshotBlueprint('versioned_ulids', null, 'v1_0_0_');
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
        $grammar = new \Plank\Snapshots\Connection\SnapshotMySqlGrammar;
        $grammar->setTablePrefix('v1_0_0_');

        $blueprint = new SnapshotBlueprint('versioneds', null, 'v1_0_0_');
        $blueprint->dropUnversionedForeign(['unversioned_id']);

        $command = collect($blueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');

        $sql = $grammar->compileDropUnversionedForeign($blueprint, $command);

        expect($sql)->toContain('alter table');
        expect($sql)->toContain('v1_0_0_versioneds');
        expect($sql)->toContain('drop foreign key');
        expect($sql)->toContain('v1_0_0_versioneds_unversioned_id_unversionedforeign');
    });

    it('compileDropUnversionedForeign produces correct Postgres SQL', function () {
        $grammar = new \Plank\Snapshots\Connection\SnapshotPostgresGrammar;
        $grammar->setTablePrefix('v1_0_0_');

        $blueprint = new SnapshotBlueprint('versioneds', null, 'v1_0_0_');
        $blueprint->dropUnversionedForeign(['unversioned_id']);

        $command = collect($blueprint->getCommands())
            ->first(fn ($cmd) => $cmd->name === 'dropUnversionedForeign');

        $sql = $grammar->compileDropUnversionedForeign($blueprint, $command);

        expect($sql)->toContain('alter table');
        expect($sql)->toContain('v1_0_0_versioneds');
        expect($sql)->toContain('drop constraint');
        expect($sql)->toContain('v1_0_0_versioneds_unversioned_id_unversionedforeign');
    });
});
