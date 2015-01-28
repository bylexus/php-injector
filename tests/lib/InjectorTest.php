<?php

/**
 * @param int $cee A cee param
 * @param string $a Something
 */
function myFancyFunctionUnderTest($a, $b, $cee = 5, $dee = 'hello') {
	return array(
			'a' => $a,
			'b' => $b,
			'cee' => $cee,
			'dee' => $dee
		);
}

class AFancyClassUnderTest {
	/**
	 * @param int $cee A cee param
	 * @param string $a Something
	 * @param int[>0] $b
	 */
	public function myFancyFunctionUnderTest($a, $b, $cee = 5, $dee = 'hello') {
		return array(
			'a' => $a,
			'b' => $b,
			'cee' => $cee,
			'dee' => $dee
		);
	}

    private function myPrivateFancyFunctionUnderTest($a) {
        return $a;
    }
}

class InjectorTest extends \PHPUnit_Framework_TestCase {
	protected static function getMethod($object, $name) {
		$class = new ReflectionClass($object);
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method;
	}

	public function test_initOptions() {
		$inj = $this->getMockBuilder('\PhpInjector\Injector')
		             ->disableOriginalConstructor()
		             ->getMock();
		$initOptions = $this->getMethod($inj,'initOptions');
		$initOptions->invoke($inj,null);
		$this->assertTrue($inj->allowUnknownParams);

		$initOptions->invoke($inj,array('allow_unknown_params'=>true));
		$this->assertTrue($inj->allowUnknownParams);

		$initOptions->invoke($inj,array('allow_unknown_params'=>false));
		$this->assertFalse($inj->allowUnknownParams);
	}

	/**
     * @expectedException Exception
     */
	public function test_initFunctionNull() {
		$inj = $this->getMockBuilder('\PhpInjector\Injector')
		             ->disableOriginalConstructor()
		             ->getMock();
		$initFunction = $this->getMethod($inj,'initFunction');
		$initFunction->invoke($inj,null);
	}

	/**
     * @expectedException Exception
     */
	public function test_initFunctionNotfound() {
		$inj = $this->getMockBuilder('\PhpInjector\Injector')
		             ->disableOriginalConstructor()
		             ->getMock();
		$initFunction = $this->getMethod($inj,'initFunction');
		$initFunction->invoke($inj,'jdhfhsdfgfhfhffhjfhjfhjshjhfjhfs');
	}

	public function test_initFunctionGood() {
		$inj = $this->getMockBuilder('\PhpInjector\Injector')
		             ->disableOriginalConstructor()
		             ->getMock();
		$initFunction = $this->getMethod($inj,'initFunction');
		$ret = $initFunction->invoke($inj,'myFancyFunctionUnderTest');
		$this->assertEquals('myFancyFunctionUnderTest',$ret);
	}

	public function test_initClosure() {
		$inj = $this->getMockBuilder('\PhpInjector\Injector')
		             ->disableOriginalConstructor()
		             ->getMock();
		$initClosure = $this->getMethod($inj,'initClosure');
		$ret = $initClosure->invoke($inj,function(){});
		$this->assertInstanceof('Closure',$ret);
	}


	/**
     * @expectedException Exception
     */
	public function test_initMethodNull() {
		$inj = $this->getMockBuilder('\PhpInjector\Injector')
		             ->disableOriginalConstructor()
		             ->getMock();
		$initMethod = $this->getMethod($inj,'initMethod');
		$initMethod->invoke($inj,array());
	}

	/**
     * @expectedException Exception
     */
	public function test_initMethodNoObject() {
		$inj = $this->getMockBuilder('\PhpInjector\Injector')
		             ->disableOriginalConstructor()
		             ->getMock();
		$initMethod = $this->getMethod($inj,'initMethod');
		$initMethod->invoke($inj,array(null,'myFancyFunctionUnderTest'));
	}

	/**
     * @expectedException Exception
     */
	public function test_initMethodNoMethod() {
		$o = new AFancyClassUnderTest();
		$inj = $this->getMockBuilder('\PhpInjector\Injector')
		             ->disableOriginalConstructor()
		             ->getMock();
		$initMethod = $this->getMethod($inj,'initMethod');
		$initMethod->invoke($inj,array($o,'unknown'));
	}

	public function test_initMethod() {
		$o = new AFancyClassUnderTest();
		$inj = $this->getMockBuilder('\PhpInjector\Injector')
		             ->disableOriginalConstructor()
		             ->getMock();
		$initMethod = $this->getMethod($inj,'initMethod');
		$ret = $initMethod->invoke($inj,array($o,'myFancyFunctionUnderTest'));
		$this->assertSame($o,$ret[0]);
		$this->assertSame('myFancyFunctionUnderTest',$ret[1]);
	}

	public function test_BuildMethodReflector() {
		$o = new AFancyClassUnderTest();
		$inj = $this->getMockBuilder('\PhpInjector\Injector')
		             ->disableOriginalConstructor()
		             ->getMock();
		$buildMethodReflector = $this->getMethod($inj,'buildMethodReflector');
		$ret = $buildMethodReflector->invoke($inj, $o, 'myPrivateFancyFunctionUnderTest');
		$this->assertInstanceof('ReflectionMethod',$ret);

        // Test private accessibility:
        $good = $ret->invoke($o,'hello');
        $this->assertEquals('hello',$good);
	}

	public function test_BuildFunctionReflector() {
		$inj = $this->getMockBuilder('\PhpInjector\Injector')
		             ->disableOriginalConstructor()
		             ->getMock();
		$buildFunctionReflector = $this->getMethod($inj,'buildFunctionReflector');
		$res = $buildFunctionReflector->invoke($inj,'myFancyFunctionUnderTest');
		$this->assertInstanceof('ReflectionFunction',$res);
	}

	public function test_parseFunctionParams() {
		$mock = $this->getMockBuilder('\PhpInjector\Injector')
		             ->disableOriginalConstructor()
		             ->setMethods(array('extractTypeInfos','getReflectionFunction'))
		             ->getMock();
		$mock->method('getReflectionFunction')->willReturn(new ReflectionFunction('myFancyFunctionUnderTest'));

		$paramStub = $this->getMockBuilder('ReflectionParameter')->disableOriginalConstructor()->getMock();
		$paramStub->method('getName')->willReturn('myParam');
		$paramStub->method('getPosition')->willReturn(1);
		$paramStub->method('isOptional')->willReturn(true);
		$paramStub->method('isDefaultValueAvailable')->willReturn(true);
		$paramStub->method('getDefaultValue')->willReturn(5);

		$mock->expects($this->once())
		     ->method('extractTypeInfos')
		     ->with(
		     	$this->isType('string'),
		     	$this->isType('array')
		     );


		$params = array($paramStub);
		$parseFunctionParams = $this->getMethod($mock,'parseFunctionParams');
		$ret = $parseFunctionParams->invokeArgs($mock,array(&$params));

		$this->assertSame(array(
			'myParam' => array(
				'name' => 'myParam',
				'position' => 1,
				'optional' => true,
				'type' => null,
				'condition' => null,
				'default_value' => 5,
			)
		),$ret);
	}

	public function test_ExtractTypeInfos() {
		$params = array(
			'a' => array(),
			'b' => array(),
			'c' => array(),
			'd' => array()
		);
		$mock = $this->getMockBuilder('\PhpInjector\Injector')
		             ->disableOriginalConstructor()
		             ->setMethods(array('matchParams'))
		             ->getMock();
		$mock->method('matchParams')->willReturn(array(
			'varname' => array('a','b','c','d'),
			'type' => array('int','int','int','string'),
			'condition' => array('','>0','0..100',''),
		));

		$extractTypeInfos = $this->getMethod($mock,'extractTypeInfos');
		$ret = $extractTypeInfos->invokeArgs($mock,array('', &$params));

		$this->assertTrue(true,$ret);
	}

	public function test_MatchParams() {
		$comment = '
		/**
		 * This is a doc comment.
		 * @param int $a Var a
		 * @param int[>0] $b Var b
		 * @param int[0..100] $c Var c
		 * @param string $d Var d
		 */
		';
		$mock = $this->getMockBuilder('\PhpInjector\Injector')
		             ->disableOriginalConstructor()
		             ->getMock();
		$matchParams = $this->getMethod($mock,'matchParams');
		$ret = $matchParams->invoke($mock,$comment);

		$this->assertSame('a',$ret['varname'][0]);
		$this->assertSame('b',$ret['varname'][1]);
		$this->assertSame('c',$ret['varname'][2]);
		$this->assertSame('d',$ret['varname'][3]);

		$this->assertSame('int',$ret['type'][0]);
		$this->assertSame('int',$ret['type'][1]);
		$this->assertSame('int',$ret['type'][2]);
		$this->assertSame('string',$ret['type'][3]);

		$this->assertSame('',$ret['condition'][0]);
		$this->assertSame('>0',$ret['condition'][1]);
		$this->assertSame('0..100',$ret['condition'][2]);
		$this->assertSame('',$ret['condition'][3]);
	}

	public function test_InvokeWithFunction() {
		$inj = new \PhpInjector\Injector('myFancyFunctionUnderTest');
		$ret = $inj->invoke(array(
			'dee' => 'ddd',
			'b' => 'bbb',
			'cee' => '55',
			'a' => 'aaa'
		));
		$this->assertSame(array(
			'a' => 'aaa',
			'b' => 'bbb',
			'cee' => 55,
			'dee' => 'ddd'
		),$ret);

		$ret = $inj->invoke(array(
			'b' => 'bbb',
			'a' => 'aaa'
		));
		$this->assertSame(array(
			'a' => 'aaa',
			'b' => 'bbb',
			'cee' => 5,
			'dee' => 'hello'
		),$ret);
	}
}
