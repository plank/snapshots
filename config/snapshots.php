<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | Snapshot:
    | This is the model which will be used to track the different versions of the 
    | content in the Application.
    |
    | It must implement the \Plank\Snapshots\Contracts\Snapshot interface.
    |
    | Existence:
    | This is the model which will be used to store what snapshots a piece of content exists in.
    */
    'models' => [
        'snapshot' => \Plank\Snapshots\Models\Snapshot::class,
        'existence' => \Plank\Snapshots\Models\Existence::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Value Objects
    |--------------------------------------------------------------------------
    |
    | version_key:
    | This object adds some helper methods for working with semantic version numbers.
    | It must implement the \Plank\Snapshots\Contracts\VersionKey interface.
    */
    'value_objects' => [
        'version_key' => \Plank\Snapshots\ValueObjects\VersionNumber::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Repositories
    |--------------------------------------------------------------------------
    |
    | Snapshots:
    | This repository will be used to retrieve and maintain the version state for the application.
    |
    | The interface is minimal to allow you to manage Snapshots in other ways if your application
    | requires it.
    |
    | It must implement the \Plank\Snapshots\Contracts\ManagesSnapshots interface.
    */
    'repositories' => [
        'snapshots' => \Plank\Snapshots\Repository\SnapshotRepository::class,
    ],

    /*
    |---------------------------------------------------------------------------
    | Observers
    |--------------------------------------------------------------------------
    |
    | Snapshots:
    | This observer is used to fire the events that create a new snapshot of your
    | Application when a new Snapshot is created.
    |
    | History:
    | This Observer is used to track the History of changes to content. Set to `null` to
    | disable history tracking.
    |
    | Identity:
    | This Observer is used to track the Identity of the content. Set to `null` to
    | disable identity tracking.
    |
    */
    'observers' => [
        'snapshot' => \Plank\Snapshots\Observers\SnapshotObserver::class,
        'existence' => \Plank\Snapshots\Observers\ExistenceObserver::class,
        'identity' => \Plank\Snapshots\Observers\IdentityObserver::class,
    ],

    /*
    |---------------------------------------------------------------------------
    | Listeners
    |--------------------------------------------------------------------------
    |
    | snapshot:
    | The snapshot listener is responsible for running the migrations and ultimately
    | kicking off the events/jobs that will copy the data to the new tables.
    |
    | copy:
    | Handles kicking off the jobs to copy data to the new tables
    |
    */
    'listeners' => [
        'snapshot' => \Plank\Snapshots\Listeners\ReleaseSnapshot::class,
        'copy' => \Plank\Snapshots\Listeners\CopyData::class,
    ],

    /*
    |---------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    |
    | The queue that snapshots jobs and data should occur on.
    |
    */
    'queue' => env('QUEUE_CONNECTION', 'sync'),

    /*
    |--------------------------------------------------------------------------
    | Force Snapshots
    |--------------------------------------------------------------------------
    |
    | When set to `true`, there will be no "unprefixed" snapshotted tables. This
    | means that a version must first exist before any versioned content can exist.
    |
    */
    'force_snapshots' => false,
];
