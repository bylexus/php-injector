<?php
use PHPUnit\Framework\TestCase;

class FloatTypeCasterTest extends TestCase
{

    public function test_cast()
    {
        $this->assertSame(0.0, \PhpInjector\FloatTypeCaster::cast(0));
        $this->assertSame(1.0, \PhpInjector\FloatTypeCaster::cast(1));
        $this->assertSame(-1.0, \PhpInjector\FloatTypeCaster::cast(-1));
        $this->assertSame(2.0, \PhpInjector\FloatTypeCaster::cast(2));
        $this->assertSame(-2.0, \PhpInjector\FloatTypeCaster::cast(-2));
        $this->assertSame(2.5, \PhpInjector\FloatTypeCaster::cast(2.5));
        $this->assertSame(2500.0, \PhpInjector\FloatTypeCaster::cast(2.5e+3));

        $this->assertSame(0.0, \PhpInjector\FloatTypeCaster::cast('0'));
        $this->assertSame(1.0, \PhpInjector\FloatTypeCaster::cast('1'));
        $this->assertSame(-1.0, \PhpInjector\FloatTypeCaster::cast('-1'));
        $this->assertSame(2.0, \PhpInjector\FloatTypeCaster::cast('2'));
        $this->assertSame(-2.0, \PhpInjector\FloatTypeCaster::cast('-2'));
        $this->assertSame(2500.0, \PhpInjector\FloatTypeCaster::cast('2.5e+3'));
        $this->assertSame(0.0, \PhpInjector\FloatTypeCaster::cast("Slex 2"));
        $this->assertSame(2.0, \PhpInjector\FloatTypeCaster::cast("2tousand"));

        $this->assertSame(0.0, \PhpInjector\FloatTypeCaster::cast(''));
        $this->assertSame(0.0, \PhpInjector\FloatTypeCaster::cast(false));
        $this->assertSame(null, \PhpInjector\FloatTypeCaster::cast(null));
        $this->assertSame(0.0, \PhpInjector\FloatTypeCaster::cast('yEs'));
        $this->assertSame(0.0, \PhpInjector\FloatTypeCaster::cast(' '));
    }
}
