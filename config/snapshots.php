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
    | Migrate on Version Creation
    |--------------------------------------------------------------------------
    |
    | This option determines whether or not to run the migrations when a new version
    | is created. If set to false, you will need to handle the migrations in your app code.
    */
    'auto_migrator' => \Plank\Snapshots\Listeners\SnapshotDatabase::class,

    /*
    |---------------------------------------------------------------------------
    | Table Data Copying
    |--------------------------------------------------------------------------
    |
    | When provided, the class will be used to automatically copy data from the the working
    | version to the newly created versions.
    */
    'copier' => \Plank\Snapshots\Listeners\TableCopier::class,

    /*
    |---------------------------------------------------------------------------
    | History
    |--------------------------------------------------------------------------
    |
    | To disable History Tracking, set this option to null. Otherwise, you must define
    | both the Observer and Labeler classes.
    |
    | Observer:
    | This is the Model Observer which will be used to track the History of changes which
    | occur on the content.
    |
    | Labeler:
    | As the package assumes "no active version" as the default approach to working on content,
    | when a new version is created we make sure all of the History events that occured on the
    | working version also get migrated to the newly created version.
    |
    | Identity:
    | This is the Model Observer which will be used to track the Identity of the content. It also
    | acts as a flag to fully enable or disable the identity management feature. Set to `null` to
    | disable all hash tracking.
    |
    */
    'history' => [
        'observer' => \Plank\Snapshots\Observers\HistoryObserver::class,
        'labeler' => \Plank\Snapshots\Listeners\LabelHistory::class,
        'identity' => \Plank\Snapshots\Observers\IdentityObserver::class,
    ],
];
