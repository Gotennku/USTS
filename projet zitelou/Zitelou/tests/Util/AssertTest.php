<?php

namespace App\Tests\Util;

use App\Util\Assert;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AssertTest extends TestCase
{
    public function testTruePasses(): void
    {
        Assert::true(true);
        $this->addToAssertionCount(1);
    }

    public function testTrueFails(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Assert::true(false, 'Expected true');
    }

    public function testFalsePasses(): void
    {
        Assert::false(false);
        $this->addToAssertionCount(1);
    }

    public function testFalseFails(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Assert::false(true);
    }

    public function testStringNotEmptyPass(): void
    {
        Assert::stringNotEmpty('abc');
        $this->addToAssertionCount(1);
    }

    public function testStringNotEmptyFails(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Assert::stringNotEmpty('');
    }

    public function testPositiveInt(): void
    {
        Assert::positiveInt(5);
        $this->addToAssertionCount(1);
    }

    public function testPositiveIntFails(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Assert::positiveInt(0);
    }

    public function testNotNullPasses(): void
    {
        Assert::notNull('x');
        $this->addToAssertionCount(1);
    }

    public function testNotNullFails(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Assert::notNull(null);
    }

    public function testInArray(): void
    {
        Assert::inArray('b', ['a','b','c']);
        $this->addToAssertionCount(1);
    }

    public function testInArrayFails(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Assert::inArray('x', ['a','b']);
    }
}
