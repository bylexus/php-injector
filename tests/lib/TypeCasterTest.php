<?php
use PHPUnit\Framework\TestCase;

class TypeCasterTest extends TestCase
{

    public function test_cast()
    {
        $this->assertSame(true, \PhpInjector\TypeCaster::cast('ON', 'bool'));
        $this->assertSame(true, \PhpInjector\TypeCaster::cast('ON', 'Boolean'));

        $this->assertSame(1, \PhpInjector\TypeCaster::cast('1', 'int'));
        $this->assertSame(1, \PhpInjector\TypeCaster::cast('1', 'inTEgEr'));

        $this->assertSame(1.0, \PhpInjector\TypeCaster::cast('1', 'float'));
        $this->assertSame(1.0, \PhpInjector\TypeCaster::cast('1', 'Double'));

        $this->assertSame("1000", \PhpInjector\TypeCaster::cast(1000, 'String'));
        $this->assertTrue(is_object(\PhpInjector\TypeCaster::cast('Hello', 'object')));

        $this->assertSame("1000", \PhpInjector\TypeCaster::cast('1000', 'mixeD'));
        $this->assertSame(1000, \PhpInjector\TypeCaster::cast(1000, 'mixeD'));
        $this->assertSame(true, \PhpInjector\TypeCaster::cast(true, 'mixeD'));
        $this->assertSame(array(2), \PhpInjector\TypeCaster::cast(array(2), 'mixeD'));

        $this->assertEquals((object) array('success' => true), \PhpInjector\TypeCaster::cast('{"success": true}', 'json'));
        $this->assertEquals(1420138800, \PhpInjector\TypeCaster::cast('1.1.2015 20:00:00+0100', 'timestamp'));
    }

    /**
     * @expectedException Exception
     */
    public function test_castException()
    {
        \PhpInjector\TypeCaster::cast('hello', 'stubborn');
    }
}
