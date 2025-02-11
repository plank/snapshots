<?php

namespace Plank\Snapshots\Contracts;

use Stringable;

interface VersionKey extends Stringable
{
    /**
     * Prefix the version to the beginning of the string
     */
    public function prefix(string $string): string;

    /**
     * Strip any occurence of the version from the string
     */
    public static function strip(string $string): string;

    /**
     * Build an instance from any string
     */
    public static function fromString(string $key): static;

    /**
     * Build an instance from a migration name
     */
    public static function fromMigrationString(string $name): static;

    /**
     * Build an instance from a version formatted string
     */
    public static function fromVersionString(string $key): static;

    /**
     * Build an instance from an identifying string
     */
    public static function fromKeyString(string $key): static;

    /**
     * Get an identifying string representation of the version
     */
    public function key(): string;
}
