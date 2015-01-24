<?php
class JsonTypeCasterTest extends \PHPUnit_Framework_TestCase {

	public function test_cast() {
		$this->assertSame(null,\PhpInjector\JsonTypeCaster::cast(''));
		$this->assertInstanceOf('stdClass',\PhpInjector\JsonTypeCaster::cast('{}'));
		$this->assertEquals(
			(object)array('a'=>'b','b'=>10,'c'=>true),
			\PhpInjector\JsonTypeCaster::cast('{"a":"b","b":10,"c":true}'));
	}
}