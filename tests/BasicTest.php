<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

class BasicTest extends TestCase
{
    public function testBasicFunctionality(): void
    {
        $this->assertTrue(true);
        $this->assertEquals(2, 1 + 1);
        $this->assertStringContainsString('test', 'This is a test string');
    }

    public function testArrayOperations(): void
    {
        $array = [1, 2, 3, 4, 5];
        
        $this->assertCount(5, $array);
        $this->assertContains(3, $array);
        $this->assertEquals(15, array_sum($array));
    }

    public function testStringOperations(): void
    {
        $string = 'SymfoShop';
        
        $this->assertEquals('SymfoShop', $string);
        $this->assertEquals(9, strlen($string));
        $this->assertStringStartsWith('Symfo', $string);
        $this->assertStringEndsWith('Shop', $string);
    }

    public function testMathematicalOperations(): void
    {
        $this->assertEquals(4, 2 * 2);
        $this->assertEquals(0, 2 - 2);
        $this->assertEquals(1, 2 / 2);
        $this->assertEquals(8, 2 ** 3);
    }

    public function testBooleanOperations(): void
    {
        $this->assertTrue(true);
        $this->assertFalse(false);
        $this->assertTrue(1 === 1);
        $this->assertFalse(1 === '1');
    }
} 