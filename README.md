<p align="center"><a href="https://plank.co"><img src="art/snapshots.png" width="100%"></a></p>

<p align="center">
<a href="https://packagist.org/packages/plank/snapshots"><img src="https://img.shields.io/packagist/php-v/plank/snapshots?color=%23fae370&label=php&logo=php&logoColor=%23fff" alt="PHP Version Support"></a>
<a href="https://laravel.com/docs/11.x/releases#support-policy"><img src="https://img.shields.io/badge/laravel-10.x,%2011.x-%2343d399?color=%23f1ede9&logo=laravel&logoColor=%23ffffff" alt="PHP Version Support"></a>
<a href="https://github.com/plank/snapshots/actions?query=workflow%3Arun-tests"><img src="https://img.shields.io/github/actions/workflow/status/plank/snapshots/run-tests.yml?branch=main&&color=%23bfc9bd&label=run-tests&logo=github&logoColor=%23fff" alt="GitHub Workflow Status"></a>
<a href="https://codeclimate.com/github/plank/snapshots/test_coverage"><img src="https://img.shields.io/codeclimate/coverage/plank/snapshots?color=%23ff9376&label=test%20coverage&logo=code-climate&logoColor=%23fff" /></a>
<a href="https://codeclimate.com/github/plank/snapshots/maintainability"><img src="https://img.shields.io/codeclimate/maintainability/plank/snapshots?color=%23528cff&label=maintainablility&logo=code-climate&logoColor=%23fff" /></a>
</p>

# Laravel Snapshots

:warning: Package is under active development. Do not use in production. :warning:

Snapshots is a Laravel package that allows you to snapshot the content of your app by replicating database tables and their content. Each snapshot represents a browseable snapshot of your app's content at a specific point in time. By changing the active snapshot of your app, you can view your app's content at a previous snapshot.

The main goal of this package is for it to perform robust snapshotting of your content, but stay out of your way. You should be able to use it without having to change your existing codebase. It should be easy to install and configure, and it should be easy to use.

## Table of Contents

- [Installation](#installation)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
  - [Snapshot Model](#snapshot-model)
  - [Repository](#repository)
  - [Auto Migration](#auto-migration)
  - [Auto Copy](#auto-copy)
- [Usage](#usage)
  - [Snapshots](#snapshots)
    - [Contract and Model](#contract-and-model)
      - [Events](#events)
    - [Repository](#snapshot-repository)
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

1. Make all migrations for snapshotted content implement `Plank\Snapshots\Migrator\SnapshotMigration`.
2. Make all models representing snapshotted content implement `Plank\Snapshots\Contracts\Snapshotted` and use the `Plank\Snapshots\Concerns\AsSnapshottedContent` trait.
3. Make all models that are not snapshotted, but have a relation to snapshotted content use the `Plank\Snapshots\Concerns\InteractsWithSnapshottedContent` trait.
4. Create a middleware to set the active snapshot of your app based on the request.

Middleware example:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Plank\Snapshots\Facades\Snapshots;

class SetActiveSnapshot
{
    public function handle($request, Closure $next)
    {
        $snapshot = $request->route('snapshot');

        if ($snapshot = Snapshots::byKey($snapshot)) {
            Snapshots::setActive($snapshot);
        }

        return $next($request);
    }
}
```

Now, whenever you create a new snapshot, the `SnapshotDatabase` listener will handle the `SnapshotCreated` event and run all migrations for the snapshotted content. It will also copy the content from the previous snapshot of the table into the new snapshot of the table.

&nbsp;

## Configuration

The package's configuration file is located at `config/snapshots.php`. If you did not publish the config file during installation, you can publish the configuration file using the following command:

```bash
php artisan vendor:publish --provider="Plank\Snapshots\SnapshotsServiceProvider" --tag="config"
```

### Snapshot Model

The `model` option is the fully qualified class name of the model that will be used to store the snapshots of your app. The default value is `Plank\Snapshots\Models\Snapshot`. Any model provided must implement the `Plank\Snapshots\Contracts\Snapshot` interface.

### Snapshot Factory

The `factory` option is the fully qualified class name of the model factory that will be used to generate Snapshot instances for testing and seeding your application. The default value is `Plank\Snapshots\Factories\SnapshotFactory`.

### Repository

The `repository` option is the fully qualified class name of the repository that will be used to retrieve the snapshots of your app. The default value is `Plank\Snapshots\Repository\SnapshotRepository`. Any repository provided must implement the `Plank\Snapshots\Contracts\ManagesSnapshots` interface.

### Auto Migration

The `auto_migrate` option determines whether the package will automatically create new tables for all snapshotted content when a new snapshot model is created. The package provides the default implementation of `Plank\Snapshots\Listeners\SnapshotDatabase`, but you can provide your own implementation.

### Auto Copy

The `auto_copy` option determines whether the package will automatically copy content to the newly snapshotted tables when a new snapshot model is created.

The package provides the default implementation of `Plank\Snapshots\Listeners\CopyTable`, where the data is copied over at the database level.

You can also provide your own implementation by setting it in the configuration file.

&nbsp;

## Usage

### Snapshots

#### Contract and Model

Snapshots are identified by and accessed through a `Snapshot` model. This model is created by the package or can be overridden by the consumer by creating a class which implements the `Plank\Contracts\Snapshot` contract, and specifying it as the [`model`](#snapshot-model) in the configuration file.

In applications that use this package, requests should generally specify an "active" `Snapshot`. The active `Snapshot` will alter the database tables which snapshotted content will be queried on.

##### Events

- `Plank\Events\SnapshotCreated`
  - Fired after a new snapshot model is created
  - Hooked on to by the package to run all the snapshotted migrations, but can be disabled by setting [`auto_migrate`](#auto-migration) to `false`

#### Snapshot Repository

The `ManagesSnapshots` interface is a minimal interface for a `Snapshot` repository required for the migrator to function. The package provides a `SnapshotRepository` class which implements this interface, but it can be overridden by the consumer by creating a class which implements the `Plank\Contracts\ManagesSnapshots` contract, and specifying it as the [`repository`](#repository) in the configuration file.

The repository is responsible for querying existing snapshots and managing the active snapshot. It is not used to create new snapshots, as that is out of scope for the package.

### Migrations

#### SnapshotMigration

This package adds a `SnapshotMigration` class, to house the migrations for all of your snapshotted content. It allows the [`SnapshotMigrator`](#snapshotmigrator) to know which migrations need to be run across all snapshots of your app.

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

You will notice that in a `SnapshotMigration` you have the `SnapshotBlueprint` class injected into your schema calls. This blueprint type exists to allow you to define plain foreign keys on snapshotted content using methods like `->plainForeign('user_id')` method.


##### Limitations

1. Foreign keys for relations from plain content to snapshotted content can not be used. This is due to there being more than one snapshot of the table, and the foreign key will not know which snapshot to reference. Foreign keys from snapshotted content to plain content, and from snapshotted content to snapshotted content are still possible.

2. It is important to note that pivot tables where at least one of the related models is snapshotted, should also be snapshotted. This is because the pivot table will need to be copied for each snapshot of the related model.

3. It is also important to note that if you are using a snapshotted custom Pivot model, you cannot relate plain content to plain content through the pivot. So be especially careful with what you are relating through your custom polymorphic pivot models.

&nbsp;

#### SnapshotMigrator

This package will replace the framework's migrator with the `SnapshotMigrator` class. The migrator extends the framework's migrator with the sole purpose of ensuring the migrations for your snapshotted content are run for every snapshot of your app.

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
2023_09_25_000002_create_snapshots_table ............................... 14ms DONE
```

When creating the first `Snapshot` model – with [`auto_migrate`](#auto-migrate) set to `true` – assuming the Page content is snapshotted, the package will re-run your `2023_09_25_000002_create_pages_table` migration for the new `Snapshot`.

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

The migration is applied to all snapshots of your content to achieve consistency across snapshots.

&nbsp;

### Models

#### Snapshotted Models

This package provides a `Plank\Snapshots\Contracts\Snapshotted` interface, and a `AsSnapshottedContent` trait. For models whose content should be snapshotted, have them implement the `Snapshotted` interface, and use the `AsSnapshottedContent` trait.

This trait ensures queries on the model's table are prefixed with the active snapshot's prefix. It also overrides the pivoted relations to use the snapshotted pivot table.

Example:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Plank\Snapshots\Concerns\AsSnapshottedContent;
use Plank\Snapshots\Contracts\Snapshotted;

class Page extends Model implements Snapshotted
{
    use AsSnapshottedContent;
}
```

#### Plain Models

For any models that have an association to a snapshotted model, you can use the `Plank\Snapshots\Concerns\InteractsWithSnapshottedContent` trait. This trait ensures that when a snapshotted model is related through a pivot, the snapshotted pivot table is used.

Example:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Plank\Snapshots\Concerns\InteractsWithSnapshottedContent;

class User extends Model
{
    use InteractsWithSnapshottedContent;

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
