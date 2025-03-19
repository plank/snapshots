<?php

use Plank\Snapshots\ValueObjects\VersionNumber;

describe('VersionNumber creates, compares and transforms correctly', function () {
    it('can be created from a string', function () {
        $number = VersionNumber::fromString('1.0.0');

        expect($number->major())->toEqual(1);
        expect($number->minor())->toEqual(0);
        expect($number->patch())->toEqual(0);
    });

    it('throws an exception if the string is invalid', function () {
        VersionNumber::fromString('10000');
    })->throws(InvalidArgumentException::class);

    it('can return the next major version', function () {
        $number = VersionNumber::fromString('1.0.0');

        $next = $number->nextMajor();

        expect($next->major())->toEqual(2);
        expect($next->minor())->toEqual(0);
        expect($next->patch())->toEqual(0);
    });

    it('can return the next minor version', function () {
        $number = VersionNumber::fromString('1.0.0');

        $next = $number->nextMinor();

        expect($next->major())->toEqual(1);
        expect($next->minor())->toEqual(1);
        expect($next->patch())->toEqual(0);
    });

    it('can return the next patch version', function () {
        $number = VersionNumber::fromString('1.0.0');

        $next = $number->nextPatch();

        expect($next->major())->toEqual(1);
        expect($next->minor())->toEqual(0);
        expect($next->patch())->toEqual(1);
    });

    it('can return a string key of the version', function () {
        $number = VersionNumber::fromString('1.0.0');

        expect($number->snake())->toEqual('1_0_0');
    });

    it('can return a kebab cased string of the version', function () {
        $number = VersionNumber::fromString('1.0.0');

        expect($number->kebab())->toEqual('1-0-0');
    });

    it('casts to a string in dot notation', function () {
        $number = VersionNumber::fromString('1.0.0');

        expect((string) $number)->toEqual('1.0.0');
    });

    it('prefixes to snake notation with a v', function () {
        $number = VersionNumber::fromString('1.0.0');

        expect($number->prefix(''))->toEqual('v1_0_0_');
    });

    it('can determine if another version number is greater than itself', function () {
        $number = VersionNumber::fromString('1.0.0');

        expect($number->isGreaterThan(VersionNumber::fromString('0.9.9')))->toBeTrue();
        expect($number->isGreaterThan(VersionNumber::fromString('1.0.0')))->toBeFalse();
        expect($number->isGreaterThan(VersionNumber::fromString('1.0.1')))->toBeFalse();
    });

    it('can determine if another version number is greater than or equal to itself', function () {
        $number = VersionNumber::fromString('1.0.0');

        expect($number->isGreaterThanOrEqualTo(VersionNumber::fromString('0.9.9')))->toBeTrue();
        expect($number->isGreaterThanOrEqualTo(VersionNumber::fromString('1.0.0')))->toBeTrue();
        expect($number->isGreaterThanOrEqualTo(VersionNumber::fromString('1.0.1')))->toBeFalse();
    });

    it('can determine if another version number is less than itself', function () {
        $number = VersionNumber::fromString('1.0.0');

        expect($number->isLessThan(VersionNumber::fromString('1.0.1')))->toBeTrue();
        expect($number->isLessThan(VersionNumber::fromString('1.0.0')))->toBeFalse();
        expect($number->isLessThan(VersionNumber::fromString('0.9.9')))->toBeFalse();
    });

    it('can determine if another version number is less than or equal to itself', function () {
        $number = VersionNumber::fromString('1.0.0');

        expect($number->isLessThanOrEqualTo(VersionNumber::fromString('1.0.1')))->toBeTrue();
        expect($number->isLessThanOrEqualTo(VersionNumber::fromString('1.0.0')))->toBeTrue();
        expect($number->isLessThanOrEqualTo(VersionNumber::fromString('0.9.9')))->toBeFalse();
    });

    it('can determine if another version number is equal to itself', function () {
        $number = VersionNumber::fromString('1.0.0');

        expect($number->isEqualTo(VersionNumber::fromString('1.0.0')))->toBeTrue();
        expect($number->isEqualTo(VersionNumber::fromString('1.0.1')))->toBeFalse();
        expect($number->isEqualTo(VersionNumber::fromString('0.9.9')))->toBeFalse();
    });
});
