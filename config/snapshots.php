<?php

use Plank\Snapshots\Jobs\CopyTable;
use Plank\Snapshots\Listeners\CopyData;
use Plank\Snapshots\Listeners\ReleaseSnapshot;
use Plank\Snapshots\Models\Existence;
use Plank\Snapshots\Models\Snapshot;
use Plank\Snapshots\Observers\ExistenceObserver;
use Plank\Snapshots\Observers\IdentityObserver;
use Plank\Snapshots\Observers\SnapshotObserver;
use Plank\Snapshots\Repository\SnapshotRepository;
use Plank\Snapshots\ValueObjects\VersionNumber;

return [
    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | Snapshot:
    | This is the model which will be used to store the different snapshots for the Application.
    | It must implement the \Plank\Snapshots\Contracts\Snapshot interface.
    |
    | Existence:
    | This is the model which will be used to store the existence of content across snapshots
    */
    'models' => [
        'snapshot' => Snapshot::class,
        'existence' => Existence::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Value Objects
    |--------------------------------------------------------------------------
    |
    | version_key:
    | This object adds some helper methods for working with snapshot numbers.
    | It must implement the \Plank\Snapshots\Contracts\VersionKey interface.
    */
    'value_objects' => [
        'version_key' => VersionNumber::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Repositories
    |--------------------------------------------------------------------------
    |
    | Snapshots:
    | This repository will be used to retrieve and maintain the snapshot state for the application.
    |
    | The interface is minimal to allow you to manage Snapshots in other ways if your application
    | requires it.
    |
    | It must implement the \Plank\Snapshots\Contracts\ManagesSnapshots interface.
    */
    'repositories' => [
        'snapshot' => SnapshotRepository::class,
    ],

    /*
    |---------------------------------------------------------------------------
    | Observers
    |--------------------------------------------------------------------------
    |
    | Snapshots:
    | This observer is used to fire snapshotting events and maintain the linked list
    | of snapshots.
    |
    | Existence:
    | This Observer is used to track the existence of content across snapshots.
    |
    | Identity:
    | This Observer is used to track the Identity of the content. Set to `null` to
    | disable identity tracking.
    |
    */
    'observers' => [
        'snapshot' => SnapshotObserver::class,
        'existence' => ExistenceObserver::class,
        'identity' => IdentityObserver::class,
    ],

    /*
    |---------------------------------------------------------------------------
    | Release
    |--------------------------------------------------------------------------
    |
    | `migrate`
    | This option determines whether or not to run the migrations when a new snapshot
    | is created. If set to false, the application code will need to handle migrations.
    |
    | `copy`
    | When provided, these settings will be used to automatically copy data to newly
    | created snapshots.
    |
    | listener:
    | The SnapshotMigrated listener is responsible for dispatching data copying jobs
    |
    | job:
    | Handles actually copying the data to the newly created snapshot. Data copying
    | jobs are dispatched with two arguments $snapshot and $table.
    |
    | queue:
    | The queue you want data copying to occur on
    |
    */
    'release' => [
        'listener' => ReleaseSnapshot::class,
        'copy' => [
            'listener' => CopyData::class,
            'job' => CopyTable::class,
            'queue' => 'sync',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Force Snapshots
    |--------------------------------------------------------------------------
    |
    | When set to `true`, there will be no "unprefixed" snapshotted tables. This
    | would mean that a snapshot must first exist in order for any snapshotted
    | content to exist.
    |
    */
    'force_snapshots' => false,
];
