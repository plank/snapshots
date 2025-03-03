<?php

namespace Plank\Snapshots\ValueObjects;

use Plank\Snapshots\Contracts\VersionKey;

class VersionNumber implements VersionKey
{
    protected const DOT_REGEX = '/(\d+\.\d+\.\d+)/';

    protected const KEY_REGEX = '/(v{0,1}\d+\_\d+\_\d+)/';

    protected const MIGRATION_REGEX = '/(v{0,1}\d+\_\d+\_\d+)/';

    public function __construct(
        protected int $major,
        protected int $minor,
        protected int $patch
    ) {}

    public static function fromString(string $string): static
    {
        if (preg_match(static::DOT_REGEX, $string) === 1) {
            return static::fromVersionString($string);
        }

        if (preg_match(static::KEY_REGEX, $string) === 1) {
            return static::fromKeyString($string);
        }

        if (preg_match(static::MIGRATION_REGEX, $string) === 1) {
            return static::fromMigrationString($string);
        }

        throw new \InvalidArgumentException('Invalid version: '.$string);
    }

    public static function fromMigrationString(string $name): static
    {
        $matches = [];

        if (preg_match(static::MIGRATION_REGEX, $name, $matches) === 1) {
            [$major, $minor, $patch] = explode('_', $matches[1]);
        } else {
            throw new \InvalidArgumentException('Invalid version: '.$name);
        }

        return new static(str($major)->after('v')->toInteger(), (int) $minor, (int) $patch);
    }

    public static function fromVersionString(string $version): static
    {
        if (preg_match(static::DOT_REGEX, $version) === 1) {
            [$major, $minor, $patch] = explode('.', $version);
        } else {
            throw new \InvalidArgumentException('Invalid version number: '.$version);
        }

        return new static((int) $major, (int) $minor, (int) $patch);
    }

    public static function fromKeyString(string $key): static
    {
        if (preg_match(static::KEY_REGEX, $key) === 1) {
            [$major, $minor, $patch] = explode('_', $key);
        } else {
            throw new \InvalidArgumentException('Invalid version key: '.$key);
        }

        return new static(str($major)->after('v')->toInteger(), (int) $minor, (int) $patch);
    }

    public function key(): string
    {
        return 'v'.$this->major.'_'.$this->minor.'_'.$this->patch;
    }

    public function prefix(string $string): string
    {
        return $this->key().'_'.static::strip($string);
    }

    public static function strip(string $string): string
    {
        return str($string)
            ->replaceMatches(static::DOT_REGEX, '')
            ->replaceMatches(static::KEY_REGEX, '')
            ->replaceMatches(static::MIGRATION_REGEX, '')
            ->trim(' _.-')
            ->toString();
    }

    protected static function isValidVersionString(string $version)
    {
        return preg_match(static::DOT_REGEX, $version) === 1
            || preg_match(static::KEY_REGEX, $version) === 1;
    }

    public function major(): int
    {
        return $this->major;
    }

    public function minor(): int
    {
        return $this->minor;
    }

    public function patch(): int
    {
        return $this->patch;
    }

    public function nextMajor(): self
    {
        return new static($this->major + 1, 0, 0);
    }

    public function nextMinor(): self
    {
        return new static($this->major, $this->minor + 1, 0);
    }

    public function nextPatch(): self
    {
        return new static($this->major, $this->minor, $this->patch + 1);
    }

    public function kebab(): string
    {
        return $this->major.'-'.$this->minor.'-'.$this->patch;
    }

    public function isGreaterThan(VersionKey|string $other): bool
    {
        $other = static::wrap($other);

        return $this->compare($other) > 0;
    }

    public function isGreaterThanOrEqualTo(VersionKey|string $other): bool
    {
        $other = static::wrap($other);

        return $this->compare($other) >= 0;
    }

    public function isLessThan(VersionKey|string $other): bool
    {
        $other = static::wrap($other);

        return $this->compare($other) < 0;
    }

    public function isLessThanOrEqualTo(VersionKey|string $other): bool
    {
        $other = static::wrap($other);

        return $this->compare($other) <= 0;
    }

    public function isEqualTo(VersionKey|string $other): bool
    {
        $other = static::wrap($other);

        return $this->compare($other) === 0;
    }

    public static function wrap(string|VersionNumber $version): self
    {
        if ($version instanceof VersionNumber) {
            return $version;
        }

        return static::fromVersionString($version);
    }

    protected function compare(self $other): int
    {
        return version_compare((string) $this, (string) $other);
    }

    public function __toString(): string
    {
        return $this->major.'.'.$this->minor.'.'.$this->patch;
    }
}
