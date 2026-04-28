<?php

use Plank\Snapshots\Jobs\CopyTable;
use Plank\Snapshots\Listeners\CopyData;
use Plank\Snapshots\Listeners\ReleaseVersion;
use Plank\Snapshots\Models\Existence;
use Plank\Snapshots\Models\Version;
use Plank\Snapshots\Observers\ExistenceObserver;
use Plank\Snapshots\Observers\IdentityObserver;
use Plank\Snapshots\Observers\VersionObserver;
use Plank\Snapshots\Repository\VersionRepository;
use Plank\Snapshots\ValueObjects\VersionNumber;

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
    | Existence:
    | This is the model which will be used to store the existence of content across snapshots
    */
    'models' => [
        'version' => Version::class,
        'existence' => Existence::class,
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
        'version_key' => VersionNumber::class,
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
    */
    'repositories' => [
        'version' => VersionRepository::class,
    ],

    /*
    |---------------------------------------------------------------------------
    | Observers
    |--------------------------------------------------------------------------
    |
    | Snapshots:
    | This observer is used to fire versioning events and maintain the linked list
    | of versions.
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
        'version' => VersionObserver::class,
        'existence' => ExistenceObserver::class,
        'identity' => IdentityObserver::class,
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
        'listener' => ReleaseVersion::class,
        'copy' => [
            'listener' => CopyData::class,
            'job' => CopyTable::class,
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
