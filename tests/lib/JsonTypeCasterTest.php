<?php
use PHPUnit\Framework\TestCase;

class JsonTypeCasterTest extends TestCase
{

    public function test_cast()
    {
        $this->assertSame(null, \PhpInjector\JsonTypeCaster::cast(''));
        $this->assertSame(null, \PhpInjector\JsonTypeCaster::cast(null));
        $this->assertInstanceOf('stdClass', \PhpInjector\JsonTypeCaster::cast('{}'));
        $this->assertEquals(
            (object) array('a' => 'b', 'b' => 10, 'c' => true),
            \PhpInjector\JsonTypeCaster::cast('{"a":"b","b":10,"c":true}')
        );
    }
}
