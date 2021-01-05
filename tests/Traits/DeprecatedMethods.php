<?php

namespace EasyQQ\Tests\Traits;

use ArrayAccess;
use PHPUnit\Framework\Constraint\ArraySubset;
use PHPUnit\Framework\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function is_array;

/**
 * Trait DeprecatedMethods
 *
 * @mixin TestCase
 */
trait DeprecatedMethods
{
    public static function assertArraySubset($subset, $array, bool $checkForObjectIdentity = false, string $message = ''): void
    {
        if (!(is_array($subset) || $subset instanceof ArrayAccess)) {
            throw InvalidArgumentException::create(
                1,
                'array or ArrayAccess'
            );
        }

        if (!(is_array($array) || $array instanceof ArrayAccess)) {
            throw InvalidArgumentException::create(
                2,
                'array or ArrayAccess'
            );
        }

        $constraint = new ArraySubset($subset, $checkForObjectIdentity);

        static::assertThat($array, $constraint, $message);
    }
}
