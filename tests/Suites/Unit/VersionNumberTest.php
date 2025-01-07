<?php

use Plank\Snapshots\ValueObjects\VersionNumber;

describe('VersionNumber creates, compares and transforms correctly', function () {
    it('can be created from a string', function () {
        $version = VersionNumber::fromVersionString('1.0.0');

        expect($version->major())->toEqual(1);
        expect($version->minor())->toEqual(0);
        expect($version->patch())->toEqual(0);
    });

    it('throws an exception if the string is invalid', function () {
        VersionNumber::fromVersionString('10000');
    })->throws(InvalidArgumentException::class);

    it('can return the next major version', function () {
        $version = VersionNumber::fromVersionString('1.0.0');

        $next = $version->nextMajor();

        expect($next->major())->toEqual(2);
        expect($next->minor())->toEqual(0);
        expect($next->patch())->toEqual(0);
    });

    it('can return the next minor version', function () {
        $version = VersionNumber::fromVersionString('1.0.0');

        $next = $version->nextMinor();

        expect($next->major())->toEqual(1);
        expect($next->minor())->toEqual(1);
        expect($next->patch())->toEqual(0);
    });

    it('can return the next patch version', function () {
        $version = VersionNumber::fromVersionString('1.0.0');

        $next = $version->nextPatch();

        expect($next->major())->toEqual(1);
        expect($next->minor())->toEqual(0);
        expect($next->patch())->toEqual(1);
    });

    it('can return a string key of the version', function () {
        $version = VersionNumber::fromVersionString('1.0.0');

        expect($version->key())->toEqual('v1_0_0');
    });

    it('can return a kebab cased string of the version', function () {
        $version = VersionNumber::fromVersionString('1.0.0');

        expect($version->kebab())->toEqual('1-0-0');
    });

    it('casts to a string in dot notation', function () {
        $version = VersionNumber::fromVersionString('1.0.0');

        expect((string) $version)->toEqual('1.0.0');
    });

    it('can determine if another version number is greater than itself', function () {
        $version = VersionNumber::fromVersionString('1.0.0');

        expect($version->isGreaterThan(VersionNumber::fromVersionString('0.9.9')))->toBeTrue();
        expect($version->isGreaterThan(VersionNumber::fromVersionString('1.0.0')))->toBeFalse();
        expect($version->isGreaterThan(VersionNumber::fromVersionString('1.0.1')))->toBeFalse();
    });

    it('can determine if another version number is greater than or equal to itself', function () {
        $version = VersionNumber::fromVersionString('1.0.0');

        expect($version->isGreaterThanOrEqualTo(VersionNumber::fromVersionString('0.9.9')))->toBeTrue();
        expect($version->isGreaterThanOrEqualTo(VersionNumber::fromVersionString('1.0.0')))->toBeTrue();
        expect($version->isGreaterThanOrEqualTo(VersionNumber::fromVersionString('1.0.1')))->toBeFalse();
    });

    it('can determine if another version number is less than itself', function () {
        $version = VersionNumber::fromVersionString('1.0.0');

        expect($version->isLessThan(VersionNumber::fromVersionString('1.0.1')))->toBeTrue();
        expect($version->isLessThan(VersionNumber::fromVersionString('1.0.0')))->toBeFalse();
        expect($version->isLessThan(VersionNumber::fromVersionString('0.9.9')))->toBeFalse();
    });

    it('can determine if another version number is less than or equal to itself', function () {
        $version = VersionNumber::fromVersionString('1.0.0');

        expect($version->isLessThanOrEqualTo(VersionNumber::fromVersionString('1.0.1')))->toBeTrue();
        expect($version->isLessThanOrEqualTo(VersionNumber::fromVersionString('1.0.0')))->toBeTrue();
        expect($version->isLessThanOrEqualTo(VersionNumber::fromVersionString('0.9.9')))->toBeFalse();
    });

    it('can determine if another version number is equal to itself', function () {
        $version = VersionNumber::fromVersionString('1.0.0');

        expect($version->isEqualTo(VersionNumber::fromVersionString('1.0.0')))->toBeTrue();
        expect($version->isEqualTo(VersionNumber::fromVersionString('1.0.1')))->toBeFalse();
        expect($version->isEqualTo(VersionNumber::fromVersionString('0.9.9')))->toBeFalse();
    });
});
