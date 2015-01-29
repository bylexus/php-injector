<?php
class OctUnitTestClass {
	public $a = 1;
}

class ObjectTypeCasterTest extends \PHPUnit_Framework_TestCase {
	public function setUp() {
		$this->obj = new stdClass();
	}

	protected function setObj($key,$value) {
		$this->obj->{$key} = $value;
		return $this->obj;
	}

	public function test_cast() {
		$this->assertSame($this->setObj('scalar',0)->scalar,\PhpInjector\ObjectTypeCaster::cast(0)->scalar);
		$this->assertSame($this->setObj('scalar',-1)->scalar,\PhpInjector\ObjectTypeCaster::cast(-1)->scalar);
		$this->assertSame($this->setObj('scalar',2500.0)->scalar,\PhpInjector\ObjectTypeCaster::cast(2.5e+3)->scalar);
		$this->assertSame($this->setObj('scalar','2.5e+3')->scalar,\PhpInjector\ObjectTypeCaster::cast('2.5e+3')->scalar);
		$this->assertSame($this->setObj('scalar',"Slex 2")->scalar,\PhpInjector\ObjectTypeCaster::cast("Slex 2")->scalar);
		$this->assertSame($this->setObj('scalar',true)->scalar,\PhpInjector\ObjectTypeCaster::cast(true)->scalar);
        $this->assertSame(null,\PhpInjector\ObjectTypeCaster::cast(null));

		$obj = new stdClass();
		$obj->{0} = 1;
		$obj->{1} = 2;
		$obj->{2} = 3;
		$this->assertEquals($obj,\PhpInjector\ObjectTypeCaster::cast(array(1,2,3)));

		$obj = new stdClass();
		$obj->one = 1;
		$obj->two = 2;
		$obj->three = 3;
		$this->assertInstanceof('OctUnitTestClass',\PhpInjector\ObjectTypeCaster::cast(new OctUnitTestClass()));
	}
}
