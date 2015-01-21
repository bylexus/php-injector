<?php

/**
 * @param int $cee A cee param
 * @param string $a Something
 */
function myFancyFunctionUnderTest($a, $b, $cee = 5, $dee = 'hello') {

}

class AFancyClassUnderTest {
	/**
	 * @param int $cee A cee param
	 * @param string $a Something
	 */
	public function myFancyFunctionUnderTest($a, $b, $cee = 5, $dee = 'hello') {

	}	
}

class PublicTestClassOfInjector extends \PhpInjector\Injector {
	public $_reflectionFunction = null;

	public function __construct() {}

	public function __call($name, $args) {
		return call_user_func_array(array($this,$name),$args);
	}

	public function __get($name) {
		return $this->{$name};
	}

	public function parseFunctionParams(array &$params) {
		return parent::parseFunctionParams($params);
	}
}

class InjectorTest extends \PHPUnit_Framework_TestCase {
	public function test_initOptions() {
		$inj = new PublicTestClassOfInjector();
		$inj->initOptions(null);
		$this->assertTrue($inj->allowUnknownParams);

		$inj->initOptions(array('allow_unknown_params'=>true));
		$this->assertTrue($inj->allowUnknownParams);

		$inj->initOptions(array('allow_unknown_params'=>false));
		$this->assertFalse($inj->allowUnknownParams);
	}

	/**
     * @expectedException Exception
     */
	public function test_initFunctionNull() {
		$inj = new PublicTestClassOfInjector();
		$inj->initFunction(null);
	}

	/**
     * @expectedException Exception
     */
	public function test_initFunctionNotfound() {
		$inj = new PublicTestClassOfInjector();
		$inj->initFunction('jdhfhsdfgfhfhffhjfhjfhjshjhfjhfs');
	}

	public function test_initFunctionGood() {
		$inj = new PublicTestClassOfInjector();
		$inj->initFunction('myFancyFunctionUnderTest');
		$this->assertSame('myFancyFunctionUnderTest',$inj->_function);
	}

	public function test_initClosure() {
		$inj = new PublicTestClassOfInjector();
		$inj->initClosure(function(){});
		$this->assertInstanceof('Closure',$inj->_function);
	}


	/**
     * @expectedException Exception
     */
	public function test_initMethodNull() {
		$inj = new PublicTestClassOfInjector();
		$inj->initMethod(array());
	}

	/**
     * @expectedException Exception
     */
	public function test_initMethodNoObject() {

		$inj = new PublicTestClassOfInjector();
		$inj->initMethod(array(null,'myFancyFunctionUnderTest'));
	}

	/**
     * @expectedException Exception
     */
	public function test_initMethodNoMethod() {
		$o = new AFancyClassUnderTest();
		$inj = new PublicTestClassOfInjector();
		$inj->initMethod(array($o,'unknown'));
	}

	public function test_initMethod() {
		$o = new AFancyClassUnderTest();
		$inj = new PublicTestClassOfInjector();
		$inj->initMethod(array($o,'myFancyFunctionUnderTest'));
		$this->assertSame($o,$inj->_object);
		$this->assertSame('myFancyFunctionUnderTest',$inj->_function);
	}

	public function test_BuildMethodReflector() {
		$o = new AFancyClassUnderTest();
		$inj = new PublicTestClassOfInjector();
		$res = $inj->buildMethodReflector($o,'myFancyFunctionUnderTest');
		$this->assertInstanceof('ReflectionMethod',$res);
	}

	public function test_BuildFunctionReflector() {
		$inj = new PublicTestClassOfInjector();
		$res = $inj->buildFunctionReflector('myFancyFunctionUnderTest');
		$this->assertInstanceof('ReflectionFunction',$res);
	}

	public function test_parseFunctionParams() {
		$mock = $this->getMockBuilder('PublicTestClassOfInjector')
		             ->disableOriginalConstructor()
		             ->setMethods(array('extractTypeInfos'))
		             ->getMock();
		$mock->_reflectionFunction = new ReflectionFunction('myFancyFunctionUnderTest');

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
		$ret = $mock->parseFunctionParams($params);

		$this->assertSame(array(
			'myParam' => array(
				'name' => 'myParam',
				'position' => 1,
				'optional' => true,
				'type' => null,
				'default_value' => 5,
			)
		),$ret);
	}
}