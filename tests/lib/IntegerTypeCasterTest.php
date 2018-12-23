<?php
use PHPUnit\Framework\TestCase;

class IntegerTypeCasterTest extends TestCase
{

    public function test_cast()
    {
        $this->assertSame(0, \PhpInjector\IntegerTypeCaster::cast(0));
        $this->assertSame(1, \PhpInjector\IntegerTypeCaster::cast(1));
        $this->assertSame(-1, \PhpInjector\IntegerTypeCaster::cast(-1));
        $this->assertSame(2, \PhpInjector\IntegerTypeCaster::cast(2));
        $this->assertSame(-2, \PhpInjector\IntegerTypeCaster::cast(-2));
        $this->assertSame(2, \PhpInjector\IntegerTypeCaster::cast(2.5));
        $this->assertSame(2500, \PhpInjector\IntegerTypeCaster::cast(2.5e+3));

        $this->assertSame(0, \PhpInjector\IntegerTypeCaster::cast('0'));
        $this->assertSame(1, \PhpInjector\IntegerTypeCaster::cast('1'));
        $this->assertSame(-1, \PhpInjector\IntegerTypeCaster::cast('-1'));
        $this->assertSame(2, \PhpInjector\IntegerTypeCaster::cast('2'));
        $this->assertSame(-2, \PhpInjector\IntegerTypeCaster::cast('-2'));
        $this->assertSame(2, \PhpInjector\IntegerTypeCaster::cast('2.5e+3'));
        $this->assertSame(0, \PhpInjector\IntegerTypeCaster::cast("Slex 2"));
        $this->assertSame(2, \PhpInjector\IntegerTypeCaster::cast("2tousand"));

        $this->assertSame(0, \PhpInjector\IntegerTypeCaster::cast(''));
        $this->assertSame(0, \PhpInjector\IntegerTypeCaster::cast(false));
        $this->assertSame(null, \PhpInjector\IntegerTypeCaster::cast(null));
        $this->assertSame(0, \PhpInjector\IntegerTypeCaster::cast('yEs'));
        $this->assertSame(0, \PhpInjector\IntegerTypeCaster::cast(' '));
    }
}
