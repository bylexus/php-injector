<?php
use PHPUnit\Framework\TestCase;

class StringTypeCasterTest extends TestCase
{

    public function test_cast()
    {
        $this->assertSame('ON', \PhpInjector\StringTypeCaster::cast('ON'));
        $this->assertSame('', \PhpInjector\StringTypeCaster::cast(''));
        $this->assertSame('', \PhpInjector\StringTypeCaster::cast(false));
        $this->assertSame(null, \PhpInjector\StringTypeCaster::cast(null));
        $this->assertSame('1', \PhpInjector\StringTypeCaster::cast(1));
        $this->assertSame('1.2', \PhpInjector\StringTypeCaster::cast(1.2));
        $this->assertSame('1', \PhpInjector\StringTypeCaster::cast(true));
    }
}
