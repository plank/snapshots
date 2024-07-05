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
    | Repositories
    |--------------------------------------------------------------------------
    |
    | Versions:
    | This is the repository which will be used retrieve and maintain the version state
    | for the Application.
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
        'table' => \Plank\Snapshots\Repository\TableRepository::class,
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
    | handler
    | When provided, the class will be used to automatically copy data from the the working
    | version to the newly created versions.
    |
    | model_events
    | When enabled, the copier will use the underlying models for each table to copy the data,
    | which will trigger any model events that are configured.
    */
    'copier' => [
        'handler' => \Plank\Snapshots\Listeners\Copier::class,
        'model_events' => false,
    ],

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
    */
    'history' => [
        'observer' => \Plank\Snapshots\Observers\HistoryObserver::class,
        'identity' => \Plank\Snapshots\Observers\IdentityObserver::class,
        'labler' => \Plank\Snapshots\Listeners\LabelHistory::class,
    ],
];
