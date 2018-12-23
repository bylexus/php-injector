<?php
use PHPUnit\Framework\TestCase;

class BooleanTypeCasterTest extends TestCase
{

    public function test_cast()
    {
        $this->assertSame(true, \PhpInjector\BooleanTypeCaster::cast('ON'));
        $this->assertSame(true, \PhpInjector\BooleanTypeCaster::cast('On'));
        $this->assertSame(true, \PhpInjector\BooleanTypeCaster::cast('T'));
        $this->assertSame(true, \PhpInjector\BooleanTypeCaster::cast('t'));
        $this->assertSame(true, \PhpInjector\BooleanTypeCaster::cast('true'));
        $this->assertSame(true, \PhpInjector\BooleanTypeCaster::cast('True'));
        $this->assertSame(true, \PhpInjector\BooleanTypeCaster::cast('tRuE'));
        $this->assertSame(true, \PhpInjector\BooleanTypeCaster::cast('yes'));
        $this->assertSame(true, \PhpInjector\BooleanTypeCaster::cast('Yes'));
        $this->assertSame(true, \PhpInjector\BooleanTypeCaster::cast('yEs'));
        $this->assertSame(true, \PhpInjector\BooleanTypeCaster::cast(true));
        $this->assertSame(true, \PhpInjector\BooleanTypeCaster::cast(1));
        $this->assertSame(true, \PhpInjector\BooleanTypeCaster::cast('1'));

        $this->assertSame(false, \PhpInjector\BooleanTypeCaster::cast('FaLsE'));
        $this->assertSame(false, \PhpInjector\BooleanTypeCaster::cast(''));
        $this->assertSame(false, \PhpInjector\BooleanTypeCaster::cast(0.9));
        $this->assertSame(false, \PhpInjector\BooleanTypeCaster::cast(0));
        $this->assertSame(false, \PhpInjector\BooleanTypeCaster::cast(-1));
        $this->assertSame(false, \PhpInjector\BooleanTypeCaster::cast(false));
        $this->assertSame(false, \PhpInjector\BooleanTypeCaster::cast('5'));
        $this->assertSame(false, \PhpInjector\BooleanTypeCaster::cast(5));
        $this->assertSame(false, \PhpInjector\BooleanTypeCaster::cast(3.4));

        $this->assertSame(null, \PhpInjector\BooleanTypeCaster::cast(null));
    }
}
