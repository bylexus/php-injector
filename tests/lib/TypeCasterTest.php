<?php
class TypeCasterTest extends \PHPUnit_Framework_TestCase {

	public function test_cast() {
		$this->assertSame(true,\PhpInjector\TypeCaster::cast('ON','bool'));
		$this->assertSame(true,\PhpInjector\TypeCaster::cast('ON','Boolean'));
		
		$this->assertSame(1,\PhpInjector\TypeCaster::cast('1','int'));
		$this->assertSame(1,\PhpInjector\TypeCaster::cast('1','inTEgEr'));

		$this->assertSame(1.0,\PhpInjector\TypeCaster::cast('1','float'));
		$this->assertSame(1.0,\PhpInjector\TypeCaster::cast('1','Double'));

		$this->assertSame("1000",\PhpInjector\TypeCaster::cast(1000,'String'));
		$this->assertTrue(is_object(\PhpInjector\TypeCaster::cast('Hello','object')));
	}
}