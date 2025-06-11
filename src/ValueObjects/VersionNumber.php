<?php

namespace Plank\Snapshots\ValueObjects;

use Plank\Snapshots\Contracts\VersionKey;
use Throwable;

class VersionNumber implements VersionKey
{
    protected const DOT_REGEX = '/(v{0,1})(\d+)\.(\d+)\.(\d+)/i';

    protected const SNAKE_REGEX = '/(v{0,1})(\d+)_(\d+)_(\d+)/i';

    protected const KEBAB_REGEX = '/(v{0,1})(\d+)-(\d+)-(\d+)/i';

    public function __construct(
        protected int $major,
        protected int $minor,
        protected int $patch
    ) {}

    public static function fromString(string $string): static
    {
        $matches = [];

        try {
            if (preg_match(static::DOT_REGEX, $string, $matches) === 1) {
                return static::fromMatches($matches);
            }

            if (preg_match(static::SNAKE_REGEX, $string, $matches) === 1) {
                return static::fromMatches($matches);
            }

            if (preg_match(static::KEBAB_REGEX, $string, $matches) === 1) {
                return static::fromMatches($matches);
            }

            throw new \InvalidArgumentException;
        } catch (Throwable) {
            throw new \InvalidArgumentException('Invalid version: '.$string);
        }
    }

    protected static function fromMatches(array $matches): static
    {
        if (count($matches) !== 5) {
            throw new \InvalidArgumentException;
        }

        return new static(
            major: (int) $matches[2],
            minor: (int) $matches[3],
            patch: (int) $matches[4],
        );
    }

    public function toString(): string
    {
        return $this->major.'.'.$this->minor.'.'.$this->patch;
    }

    public function snake(): string
    {
        return $this->major.'_'.$this->minor.'_'.$this->patch;
    }

    public function kebab(): string
    {
        return $this->major.'-'.$this->minor.'-'.$this->patch;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function prefix(string $string): string
    {
        return 'v'.$this->snake().'_'.static::strip($string);
    }

    public function isPrefixOf(string $string): bool
    {
        return str($string)->startsWith([$this->snake(), $this->kebab()]);
    }

    public static function strip(string $string): string
    {
        $stripDot = str(static::DOT_REGEX)
            ->beforeLast('/i')
            ->append('[\.]{0,1}/i')
            ->value();

        $stripSnake = str(static::SNAKE_REGEX)
            ->beforeLast('/i')
            ->append('_{0,1}/i')
            ->value();

        $stripKebab = str(static::KEBAB_REGEX)
            ->beforeLast('/i')
            ->append('-{0,1}/i')
            ->value();

        return str($string)
            ->replaceMatches($stripDot, '')
            ->replaceMatches($stripSnake, '')
            ->replaceMatches($stripKebab, '')
            ->trim(' _.-')
            ->toString();
    }

    protected static function isValidVersionString(string $version)
    {
        return preg_match(static::DOT_REGEX, $version) === 1
            || preg_match(static::SNAKE_REGEX, $version) === 1
            || preg_match(static::KEBAB_REGEX, $version) === 1;
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

        return static::fromString($version);
    }

    protected function compare(self $other): int
    {
        return version_compare((string) $this, (string) $other);
    }
}
