<?php

namespace Plank\Snapshots\ValueObjects;

class VersionNumber
{
    protected const REGEX = '/^(\d+\.\d+\.\d+)$/';

    public function __construct(
        protected int $major,
        protected int $minor,
        protected int $patch
    ) {
    }

    public static function fromVersionString(string $number): self
    {
        if (! static::isValidVersionString($number)) {
            throw new \InvalidArgumentException('Invalid version number: '.$number);
        }

        [$major, $minor, $patch] = explode('.', $number);

        return new static((int) $major, (int) $minor, (int) $patch);
    }

    protected static function isValidVersionString(string $version)
    {
        return preg_match(static::REGEX, $version) === 1;
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

    public function snake(): string
    {
        return $this->major.'_'.$this->minor.'_'.$this->patch;
    }

    public function kebab(): string
    {
        return $this->major.'-'.$this->minor.'-'.$this->patch;
    }

    public function isGreaterThan(self $other): bool
    {
        return $this->compare($other) > 0;
    }

    public function isGreaterThanOrEqualTo(self $other): bool
    {
        return $this->compare($other) >= 0;
    }

    public function isLessThan(self $other): bool
    {
        return $this->compare($other) < 0;
    }

    public function isLessThanOrEqualTo(self $other): bool
    {
        return $this->compare($other) <= 0;
    }

    public function isEqualTo(self $other): bool
    {
        return $this->compare($other) === 0;
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
