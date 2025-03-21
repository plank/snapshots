<p align="center"><a href="https://plank.co"><img src="art/snapshots.png" width="100%"></a></p>

<p align="center">
<a href="https://packagist.org/packages/plank/snapshots"><img src="https://img.shields.io/packagist/php-v/plank/snapshots?color=%23fae370&label=php&logo=php&logoColor=%23fff" alt="PHP Version Support"></a>
<a href="https://laravel.com/docs/11.x/releases#support-policy"><img src="https://img.shields.io/badge/laravel-10.x,%2011.x-%2343d399?color=%23f1ede9&logo=laravel&logoColor=%23ffffff" alt="PHP Version Support"></a>
<a href="https://github.com/plank/snapshots/actions?query=workflow%3Arun-tests"><img src="https://img.shields.io/github/actions/workflow/status/plank/snapshots/run-tests.yml?branch=main&&color=%23bfc9bd&label=run-tests&logo=github&logoColor=%23fff" alt="GitHub Workflow Status"></a>
<a href="https://codeclimate.com/github/plank/snapshots/test_coverage"><img src="https://img.shields.io/codeclimate/coverage/plank/snapshots?color=%23ff9376&label=test%20coverage&logo=code-climate&logoColor=%23fff" /></a>
<a href="https://codeclimate.com/github/plank/snapshots/maintainability"><img src="https://img.shields.io/codeclimate/maintainability/plank/snapshots?color=%23528cff&label=maintainablility&logo=code-climate&logoColor=%23fff" /></a>
</p>

# Laravel Snapshots

:warning: Package is under active development. Wait for v1.0.0 for production use. :warning:

Snapshots is a Laravel package that allows you to version the content of your app by replicating database tables and their content. Each snapshot represents a browseable version of your app's content at a specific point in time. By changing the active version of your app, you can view your app's content at a previous version.

The main goal of this package is for it to perform robust versioning of your content, but stay out of your way. You should be able to use it without having to change your existing codebase. It should be easy to install and configure, and it should be easy to use.

## Table of Contents

- [Installation](#installation)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
  - [Version Model](#version-model)
  - [Repository](#repository)
  - [Auto Migration](#auto-migration)
  - [Auto Copy](#auto-copy)
- [Usage](#usage)
  - [Versions](#versions)
    - [Contract and Model](#contract-and-model)
      - [Events](#events)
    - [Repository](#version-repository)
  - [Migrations](#migrations)
    - [SnapshotMigration](#snapshotmigration)
    - [SnapshotMigrator](#snapshotmigrator)
      - [Limitations](#limitations)
  - [Models](#models)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)
- [Security Vulnerabilities](#security-vulnerabilities)
- [Plank](#check-us-out)

&nbsp;

## Installation

You can install the package via composer:

```bash
composer require plank/snapshots
```

You can use the package's install command to complete the installation:

```bash
php artisan snapshots:install
```

## Quick Start

Once the installation has completed, to begin using the package:

1. Make all migrations for versioned content implement `Plank\Snapshots\Migrator\SnapshotMigration`.
2. Make all models representing versioned content implement `Plank\Snapshots\Contracts\Versioned` and use the `Plank\Snapshots\Concerns\AsVersionedContent` trait.
3. Make all models that are not versioned, but have a relation to versioned content use the `Plank\Snapshots\Concerns\InteractsWithVersionedContent` trait.
4. Create a middleware to set the active version of your app based on the request.

Middleware example:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Plank\Snapshots\Facades\Versions;

class SetActiveVersion
{
    public function handle($request, Closure $next)
    {
        $version = $request->route('version');

        if ($version = Versions::byKey($version)) {
            Versions::setActive($version);
        }

        return $next($request);
    }
}
```

Now, whenever you create a new version, the `SnapshotDatabase` listener will handle the `VersionCreated` event and run all migrations for the versioned content. It will also copy the content from the previous version of the table into the new version of the table.

&nbsp;

## Configuration

The package's configuration file is located at `config/snapshots.php`. If you did not publish the config file during installation, you can publish the configuration file using the following command:

```bash
php artisan vendor:publish --provider="Plank\Snapshots\SnapshotsServiceProvider" --tag="config"
```

### Version Model

The `model` option is the fully qualified class name of the model that will be used to store the versions of your app. The default value is `Plank\Snapshots\Models\Version`. Any model provided must implement the `Plank\Snapshots\Contracts\Version` interface.

### Version Factory

The `factory` option is the fully qualified class name of the model factory that will be used to generate Version instances for testing and seeding your application. The default value is `Plank\Snapshots\Factories\VersionFactory`.

### Repository

The `repository` option is the fully qualified class name of the repository that will be used to retrieve the versions of your app. The default value is `Plank\Snapshots\Repository\VersionRepository`. Any repository provided must implement the `Plank\Snapshots\Contracts\ManagesVersions` interface.

### Auto Migration

The `auto_migrate` option determines whether the package will automatically create new tables for all versioned content when a new version model is created. The package provides the default implementation of `Plank\Snapshots\Listeners\SnapshotDatabase`, but you can provide your own implementation.

### Auto Copy

The `auto_copy` option determines whether the package will automatically copy content to the newly versioned tables when a new version model is created.

The package provides the default implementation of `Plank\Snapshots\Listeners\CopyTable`, where the data is copied over at the database level.

The package also ships with `Plank\Snapshots\Listeners\CopyModels`, where the data is copied over at the model level. This is useful if you have custom logic in your models that needs to be run when the data is copied over.

You can also provide your own implementation by setting it in the configuration file.

&nbsp;

## Usage

### Versions

#### Contract and Model

Snapshots are identified by and accessed through a `Version` model. This model is created by the package or can be overridden by the consumer by creating a class which implements the `Plank\Contracts\Version` contract, and specifying it as the [`model`](#version-model) in the configuration file.

In applications that use this package, requests should generally specify an "active" `Version`. The active `Version` will alter the database tables which versioned content will be queried on.

##### Events

- `Plank\Events\VersionCreated`
  - Fired after a new version model is created
  - Hooked on to by the package to run all the versioned migrations, but can be disabled by setting [`auto_migrate`](#auto-migration) to `false`

#### Version Repository

The `ManagesVersions` interface is a minimal interface for a `Version` repository required for the migrator to function. The package provides a `VersionRepository` class which implements this interface, but it can be overridden by the consumer by creating a class which implements the `Plank\Contracts\ManagesVersions` contract, and specifying it as the [`repository`](#repository) in the configuration file.

The repository is responsible for querying existing versions and managing the active version. It is not used to create new versions, as that is out of scope for the package.

### Migrations

#### SnapshotMigration

This package adds a `SnapshotMigration` class, to house the migrations for all of your versioned content. It allows the [`SnapshotMigrator`](#snapshotmigrator) to know which migrations need to be run across all versions of your app.

To use it, simply make the Migration classes extend `SnapshotMigration` instead of the framework's `Migration` class.

```php
<?php

use Plank\Snapshots\Migrations\SnapshotBlueprint;
use Plank\Snapshots\Migrator\SnapshotMigration;

return new class extends SnapshotMigration
{
    public function up()
    {
        Schema::create('blocks', function (SnapshotBlueprint $table) {
            $table->id();
            $table->unsignedBigInteger('page_id');
            $table->string('name');
            $table->morphs('blockable');
            $table->timestamps();

            $table->foreign('page_id')->references('id')->on('pages');
        });
    }

    public function down()
    {
        Schema::dropIfExists('blocks');
    }
}
```

You will notice that in a `SnapshotMigration` you have the `SnapshotBlueprint` class injected into your schema calls. This blueprint type exists to allow you to define unversioned foreign keys on versioned content using methods like `->unversionedForeign('user_id')` method.


##### Limitations

1. Foreign keys for relations from unversioned content to versioned content can not be used. This is due to there being more than one version of the table, and the foreign key will not know which version to reference. Foreign keys from versioned content to unversioned content, and from versioned content to versioned content are still possible.

2. It is important to note that pivot tables where at least one of the related models is versioned, should also be versioned. This is because the pivot table will need to be copied for each version of the related model.

3. It is also important to note that if you are using a versioned custom Pivot model, you cannot relate unversioned content to unversioned content through the pivot. So be especially careful with what you are relating through your custom polymorphic pivot models.

&nbsp;

#### SnapshotMigrator

This package will replace the framework's migrator with the `SnapshotMigrator` class. The migrator extends the framework's migrator with the sole purpose of ensuring the migrations for your versioned content are run for every version of your app.

For example, after running migrations in a traditional Laravel Application, you might have the following:

```bash
php artisan migrate

INFO  Preparing database.

Creating migration table ............................... 13ms DONE

INFO  Running migrations.

2023_09_25_000000_create_users_table ............................... 10ms DONE
2023_09_25_000001_create_roles_table ............................... 10ms DONE
2023_09_25_000002_create_pages_table ............................... 14ms DONE
```

However, in a Laravel Application using Snapshots, you might have the following after the initial migration:

```bash
php artisan migrate

INFO  Preparing database.

Creating migration table ............................... 13ms DONE

INFO  Running migrations.

2023_09_25_000000_create_users_table    ............................... 10ms DONE
2023_09_25_000001_create_roles_table    ............................... 10ms DONE
2023_09_25_000002_create_pages_table    ............................... 14ms DONE
2023_09_25_000002_create_versions_table ............................... 14ms DONE
```

When creating the first `Version` model – with [`auto_migrate`](#auto-migrate) set to `true` – assuming the Page content is versioned, the package will re-run your `2023_09_25_000002_create_pages_table` migration for the new `Version`.

The following output is shown as an illustration. The migration is run in the background, and not output.

```bash
php artisan migrate

INFO  Running migrations.

v1_0_0_2023_09_25_000000_create_pages_table    ............................... 10ms DONE
```

Finally, assume you add a new migration `2023_09_25_000003_add_slug_to_pages_table` to add a `slug` column to the `pages` table, and you run the migrations on deploy.

```bash
php artisan migrate

INFO  Running migrations.

2023_09_25_000003_add_slug_to_pages_table         ............................... 10ms DONE
v1_0_0_2023_09_25_000003_add_slug_to_pages_table  ............................... 10ms DONE
```

The migration is applied to all versions of your content to achieve consistency across versions.

&nbsp;

### Models

#### Versioned Models

This package provides a `Plank\Snapshots\Contracts\Versioned` interface, and a `AsVersionedContent` trait. For models whose content should be versioned, have them implement the `Versioned` interface, and use the `AsVersionedContent` trait.

This trait ensures queries on the model's table are prefixed with the active version's prefix. It also overrides the pivoted relations to use the versioned pivot table.

Example:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Plank\Snapshots\Concerns\AsVersionedContent;
use Plank\Snapshots\Contracts\Versioned;

class Page extends Model implements Versioned
{
    use AsVersionedContent;
}
```

#### Unversioned Models

For any models that have an association to a versioned model, you can use the `Plank\Snapshots\Concerns\InteractsWithVersionedContent` trait. This trait ensures that when a versioned model is related through a pivot, the versioned pivot table is used.

Example:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Plank\Snapshots\Concerns\InteractsWithVersionedContent;

class User extends Model
{
    use InteractsWithVersionedContent;

    public function pages()
    {
        return $this->belongsToMany(Page::class);
    }
}
```

&nbsp;


## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

&nbsp;

## Credits

- [Kurt Friars](https://github.com/kfriars)
- [Massimo Triassi](https://github.com/m-triassi)
- [Andrew Hanichkovsky](https://github.com/a-drew)
- [All Contributors](../../contributors)

&nbsp;

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

&nbsp;

## Security Vulnerabilities

If you discover a security vulnerability within siren, please send an e-mail to [security@plank.co](mailto:security@plank.co). All security vulnerabilities will be promptly addressed.

&nbsp;

## Check Us Out!

<a href="https://plank.co/open-source/learn-more-image">
    <img src="https://plank.co/open-source/banner">
</a>

&nbsp;

Plank focuses on impactful solutions that deliver engaging experiences to our clients and their users. We're committed to innovation, inclusivity, and sustainability in the digital space. [Learn more](https://plank.co/open-source/learn-more-link) about our mission to improve the web.
