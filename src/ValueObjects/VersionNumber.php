<?php

namespace Plank\Snapshots\ValueObjects;

use Plank\Snapshots\Contracts\VersionKey;

class VersionNumber implements VersionKey
{
    protected const DOT_REGEX = '/^(\d+\.\d+\.\d+)$/';

    protected const KEY_REGEX = '/^(v{0,1}\d+\_\d+\_\d+)$/';

    public function __construct(
        protected int $major,
        protected int $minor,
        protected int $patch
    ) {}

    public static function fromVersionString(string $number): static
    {
        if (preg_match(static::DOT_REGEX, $number) === 1) {
            [$major, $minor, $patch] = explode('.', $number);
        } elseif (preg_match(static::KEY_REGEX, $number) === 1) {
            [$major, $minor, $patch] = explode('_', $number);
        } else {
            throw new \InvalidArgumentException('Invalid version number: '.$number);
        }

        return new static(str($major)->after('v')->toInteger(), (int) $minor, (int) $patch);
    }

    public function key(): string
    {
        return 'v'.$this->major.'_'.$this->minor.'_'.$this->patch;
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
