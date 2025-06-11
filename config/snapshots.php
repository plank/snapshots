<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | Version:
    | This is the model which will be used to store the different versions for the Application.
    | It must implement the \Plank\Snapshots\Contracts\Version interface.
    |
    | History:
    | This is the model which will be used to store the history of changes for the Application.
    */
    'models' => [
        'version' => \Plank\Snapshots\Models\Version::class,
        'history' => \Plank\Snapshots\Models\History::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Value Objects
    |--------------------------------------------------------------------------
    |
    | version_key:
    | This object adds some helper methods for working with version numbers.
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
    | Versions:
    | This repository will be used to retrieve and maintain the version state for the application.
    |
    | The interface is minimal to allow you to manage Versions in other ways if your application
    | requires it.
    |
    | It must implement the \Plank\Snapshots\Contracts\ManagesVersions interface.
    |
    | Causers:
    | This is the repository which will be used retrieve and maintain the causer state
    | for the Application. The causer is the model or object responsible for the changes
    | being made to the content.
    */
    'repositories' => [
        'version' => \Plank\Snapshots\Repository\VersionRepository::class,
        'causer' => \Plank\Snapshots\Repository\CauserRepository::class,
    ],

    /*
    |---------------------------------------------------------------------------
    | Observers
    |--------------------------------------------------------------------------
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
        'version' => \Plank\Snapshots\Observers\VersionObserver::class,
        'history' => \Plank\Snapshots\Observers\HistoryObserver::class,
        'identity' => \Plank\Snapshots\Observers\IdentityObserver::class,
    ],

    /*
    |---------------------------------------------------------------------------
    | Release
    |--------------------------------------------------------------------------
    |
    | `migrate`
    | This option determines whether or not to run the migrations when a new version
    | is created. If set to false, the application code will need to handle migrations.
    |
    | `copy`
    | When provided, these settings will be used to automatically copy data to newly
    | created versions.
    |
    | listener:
    | The VersionMigrated listener is responsible for dispatching data copying jobs
    |
    | job:
    | Handles actually copying the data to the newly created version. Data copying
    | jobs are dispatched with two arguments $version and $table.
    |
    | queue:
    | The queue you want data copying to occur on
    |
    */
    'release' => [
        'listener' => \Plank\Snapshots\Listeners\ReleaseVersion::class,
        'copy' => [
            'job' => \Plank\Snapshots\Jobs\CopyTable::class,
            'listener' => \Plank\Snapshots\Listeners\CopyData::class,
            'queue' => 'sync',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Force Versions
    |--------------------------------------------------------------------------
    |
    | When set to `true`, there will be no "unprefixed" versioned tables. This
    | would mean that a version must first exist in order for any versioned
    | content to exist.
    |
    */
    'force_versions' => false,
];
