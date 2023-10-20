<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Version Model
    |--------------------------------------------------------------------------
    |
    | This is the model which will be used to store the different versions for the Application.
    | It must implement the \Plank\Snapshots\Contracts\Version interface.
    |
    */
    'model' => \Plank\Snapshots\Models\Version::class,

    /*
    |--------------------------------------------------------------------------
    | Version Factory
    |--------------------------------------------------------------------------
    |
    | This is the factory which will be used to generate new versions for
    | tests and seeders.
    |
    */
    'factory' => \Plank\Snapshots\Factories\VersionFactory::class,

    /*
    |--------------------------------------------------------------------------
    | Version Repository
    |--------------------------------------------------------------------------
    |
    | This is the repository which will be used to store, retrieve and maintain the version state
    | for the Application.
    |
    | The interface is minimal to allow you to manage Versions in other ways if your application
    | requires it.
    |
    | It must implement the \Plank\Snapshots\Contracts\ManagesVersions interface.
    */
    'repository' => \Plank\Snapshots\Repository\VersionRepository::class,

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
    | Copy Tables on Creation
    |--------------------------------------------------------------------------
    |
    | This option determines whether or not to copy the tables from the previous version
    | when a new version is created. If set to false, you will need to handle the copying
    | in your app code.
    */
    'auto_copier' => \Plank\Snapshots\Listeners\CopyTable::class,
];
