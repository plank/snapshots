<?php

namespace Plank\Snapshots\Contracts;

use Stringable;

interface SnapshotKey extends Stringable
{
    /**
     * Build an instance from any string
     */
    public static function fromString(string $key): static;

    /**
     * Get an identifying string representation of the snapshot
     */
    public function toString(): string;

    /**
     * Prefix the snapshot to the beginning of the string
     */
    public function snake(): string;

    /**
     * Strip any occurence of the snapshot from the string
     */
    public function kebab(): string;

    /**
     * Prefix the snapshot to the beginning of the string
     */
    public function prefix(string $string): string;

    /**
     * Determine if this key is the prefix of the given string
     */
    public function isPrefixOf(string $string): bool;

    /**
     * Strip any occurence of the snapshot from the string
     */
    public static function strip(string $string): string;
}
