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
    | Migrations Path
    |--------------------------------------------------------------------------
    |
    | This is the path to your migrations directory. If you have your migrations in
    | a non-default folder, specify the real path, to the folder
    | ie: base_path('custom/migrations')
    |
    | If using the default migrations folder, leave this value null.
    */
    'migration_path' => null,

    /*
    |---------------------------------------------------------------------------
    | Migrate on Version Creation
    |--------------------------------------------------------------------------
    |
    | This option determines whether or not to run the migrations when a new version
    | is created. If set to false, you will need to handle the migrations in your app code.
    */
    'auto_migrate' => true,

    /*
    |---------------------------------------------------------------------------
    | Copy Tables on Creation
    |--------------------------------------------------------------------------
    |
    | This option determines whether or not to copy the tables from the previous version
    | when a new version is created. If set to false, you will need to handle the copying
    | in your app code.
    */
    'auto_copy' => true,
];
