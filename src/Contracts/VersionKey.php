<?php

namespace Plank\Snapshots\Contracts;

use Stringable;

interface VersionKey extends Stringable
{
    /**
     * Build an instance from any string
     */
    public static function fromString(string $key): static;

    /**
     * Get an identifying string representation of the version
     */
    public function toString(): string;

    /**
     * Prefix the version to the beginning of the string
     */
    public function snake(): string;

    /**
     * Strip any occurence of the version from the string
     */
    public function kebab(): string;

    /**
     * Prefix the version to the beginning of the string
     */
    public function prefix(string $string): string;

    /**
     * Determine if this key is the prefix of the given string
     */
    public function isPrefixOf(string $string): bool;

    /**
     * Strip any occurence of the version from the string
     */
    public static function strip(string $string): string;
}
