<?php

use PHPUnit\Framework\TestCase;

/**
 * @param int $cee A cee param
 * @param string $a Something
 */
function myFancyFunctionUnderTest($a, float $b, $cee = 5, $dee = 'hello') {
    return array(
        'a' => $a,
        'b' => $b,
        'cee' => $cee,
        'dee' => $dee,
    );
}

class AFancyClassUnderTest {
    /**
     * @param int $cee A cee param
     * @param string $a Something
     * @param int[>0] $b
     */
    public function myFancyFunctionUnderTest($a, float $b, $cee = 5, $dee = 'hello') {
        return array(
            'a' => $a,
            'b' => $b,
            'cee' => $cee,
            'dee' => $dee,
        );
    }

    public function myOtherFancyFunctionUnderTest($a) {
        return $a;
    }
}

/**
 * @param AFancyClassUnderTest $obj
 * @param float $zahl
 */
function objectInjectionFunction(AFancyClassUnderTest $obj, int $zahl, \ArrayObject $ao) {
    return [
        'AFancyClassUnderTest' => $obj,
        'zahl' => $zahl,
        'ArrayObject' => $ao,
    ];
}

class TestServiceContainer implements \Psr\Container\ContainerInterface {
    public $service = null;
    public $hasService = true;
    public function get($id) {
        return $this->service;
    }
    public function has($id): bool {
        return $this->hasService;
    }
}

class InjectorTest extends TestCase {
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
        $initOptions = $this->getMethod($inj, 'initOptions');
        $initOptions->invoke($inj, null);
        $this->assertTrue($inj->allowUnknownParams);

        $initOptions->invoke($inj, array('allow_unknown_params' => true));
        $this->assertTrue($inj->allowUnknownParams);

        $initOptions->invoke($inj, array('allow_unknown_params' => false));
        $this->assertFalse($inj->allowUnknownParams);
    }

    public function test_initFunctionNull() {
        $this->expectException(\Exception::class);
        $inj = $this->getMockBuilder('\PhpInjector\Injector')
            ->disableOriginalConstructor()
            ->getMock();
        $initFunction = $this->getMethod($inj, 'initFunction');
        $initFunction->invoke($inj, null);
    }

    public function test_initFunctionNotfound() {
        $this->expectException(\Exception::class);
        $inj = $this->getMockBuilder('\PhpInjector\Injector')
            ->disableOriginalConstructor()
            ->getMock();
        $initFunction = $this->getMethod($inj, 'initFunction');
        $initFunction->invoke($inj, 'jdhfhsdfgfhfhffhjfhjfhjshjhfjhfs');
    }

    public function test_initFunctionGood() {
        $inj = $this->getMockBuilder('\PhpInjector\Injector')
            ->disableOriginalConstructor()
            ->getMock();
        $initFunction = $this->getMethod($inj, 'initFunction');
        $ret = $initFunction->invoke($inj, 'myFancyFunctionUnderTest');
        $this->assertEquals('myFancyFunctionUnderTest', $ret);
    }

    public function test_initClosure() {
        $inj = $this->getMockBuilder('\PhpInjector\Injector')
            ->disableOriginalConstructor()
            ->getMock();
        $initClosure = $this->getMethod($inj, 'initClosure');
        $ret = $initClosure->invoke($inj, function () {
        });
        $this->assertInstanceof('Closure', $ret);
    }

    public function test_initMethodNull() {
        $this->expectException(\Exception::class);
        $inj = $this->getMockBuilder('\PhpInjector\Injector')
            ->disableOriginalConstructor()
            ->getMock();
        $initMethod = $this->getMethod($inj, 'initMethod');
        $initMethod->invoke($inj, array());
    }

    public function test_initMethodNoObject() {
        $this->expectException(\Exception::class);
        $inj = $this->getMockBuilder('\PhpInjector\Injector')
            ->disableOriginalConstructor()
            ->getMock();
        $initMethod = $this->getMethod($inj, 'initMethod');
        $initMethod->invoke($inj, array(null, 'myFancyFunctionUnderTest'));
    }

    public function test_initMethodNoMethod() {
        $this->expectException(\Exception::class);
        $o = new AFancyClassUnderTest();
        $inj = $this->getMockBuilder('\PhpInjector\Injector')
            ->disableOriginalConstructor()
            ->getMock();
        $initMethod = $this->getMethod($inj, 'initMethod');
        $initMethod->invoke($inj, array($o, 'unknown'));
    }

    public function test_initMethod() {
        $o = new AFancyClassUnderTest();
        $inj = $this->getMockBuilder('\PhpInjector\Injector')
            ->disableOriginalConstructor()
            ->getMock();
        $initMethod = $this->getMethod($inj, 'initMethod');
        $ret = $initMethod->invoke($inj, array($o, 'myFancyFunctionUnderTest'));
        $this->assertSame($o, $ret[0]);
        $this->assertSame('myFancyFunctionUnderTest', $ret[1]);
    }

    public function test_BuildMethodReflector() {
        $o = new AFancyClassUnderTest();
        $inj = $this->getMockBuilder('\PhpInjector\Injector')
            ->disableOriginalConstructor()
            ->getMock();
        $buildMethodReflector = $this->getMethod($inj, 'buildMethodReflector');
        $ret = $buildMethodReflector->invoke($inj, $o, 'myOtherFancyFunctionUnderTest');
        $this->assertInstanceof('ReflectionMethod', $ret);

        // Test invocation:
        $good = $ret->invoke($o, 'hello');
        $this->assertEquals('hello', $good);
    }

    public function test_BuildFunctionReflector() {
        $inj = $this->getMockBuilder('\PhpInjector\Injector')
            ->disableOriginalConstructor()
            ->getMock();
        $buildFunctionReflector = $this->getMethod($inj, 'buildFunctionReflector');
        $res = $buildFunctionReflector->invoke($inj, 'myFancyFunctionUnderTest');
        $this->assertInstanceof('ReflectionFunction', $res);
    }

    public function test_parseFunctionParams() {
        $mock = $this->getMockBuilder('\PhpInjector\Injector')
            ->disableOriginalConstructor()
            ->onlyMethods(array('extractTypeInfos', 'getReflectionFunction'))
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
        $parseFunctionParams = $this->getMethod($mock, 'parseFunctionParams');
        $ret = $parseFunctionParams->invokeArgs($mock, array(&$params));

        $this->assertSame(array(
            'myParam' => array(
                'name' => 'myParam',
                'position' => 1,
                'optional' => true,
                'type' => null,
                'condition' => null,
                'default_value' => 5,
            ),
        ), $ret);
    }

    public function test_ExtractTypeInfos() {
        $params = array(
            'a' => array(),
            'b' => array(),
            'c' => array(),
            'd' => array(),
        );
        $mock = $this->getMockBuilder('\PhpInjector\Injector')
            ->disableOriginalConstructor()
            ->onlyMethods(array('matchParams'))
            ->getMock();
        $mock->method('matchParams')->willReturn(array(
            'varname' => array('a', 'b', 'c', 'd'),
            'type' => array('int', 'int', 'int', 'string'),
            'condition' => array('', '>0', '0..100', ''),
        ));

        $extractTypeInfos = $this->getMethod($mock, 'extractTypeInfos');
        $extractTypeInfos->invokeArgs($mock, array('', &$params));
        foreach ($params as $param) {
            $this->assertTrue(count($param) > 0);
        }
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
        $matchParams = $this->getMethod($mock, 'matchParams');
        $ret = $matchParams->invoke($mock, $comment);

        $this->assertSame('a', $ret['varname'][0]);
        $this->assertSame('b', $ret['varname'][1]);
        $this->assertSame('c', $ret['varname'][2]);
        $this->assertSame('d', $ret['varname'][3]);

        $this->assertSame('int', $ret['type'][0]);
        $this->assertSame('int', $ret['type'][1]);
        $this->assertSame('int', $ret['type'][2]);
        $this->assertSame('string', $ret['type'][3]);

        $this->assertSame('', $ret['condition'][0]);
        $this->assertSame('>0', $ret['condition'][1]);
        $this->assertSame('0..100', $ret['condition'][2]);
        $this->assertSame('', $ret['condition'][3]);
    }

    public function test_InvokeWithFunction() {
        $inj = new \PhpInjector\Injector('myFancyFunctionUnderTest');
        $ret = $inj->invoke(array(
            'dee' => 'ddd',
            'b' => "3.5",
            'cee' => '55',
            'a' => 'aaa',
        ));
        $this->assertSame(array(
            'a' => 'aaa',
            'b' => 3.5,
            'cee' => 55,
            'dee' => 'ddd',
        ), $ret);

        $ret = $inj->invoke(array(
            'b' => '4.5',
            'a' => 'aaa',
        ));
        $this->assertSame(array(
            'a' => 'aaa',
            'b' => 4.5,
            'cee' => 5,
            'dee' => 'hello',
        ), $ret);
    }

    public function test_FindParamValueWithType() {
        $mock = $this->getMockBuilder('\PhpInjector\Injector')
            ->disableOriginalConstructor()
            ->onlyMethods(array())
            ->getMock();
        $inst = new AFancyClassUnderTest();
        $params = [[
            'zahl' => 5.5,
            'AFancyClassUnderTest' => $inst,
        ], AFancyClassUnderTest::class];

        $findParamValueWithType = $this->getMethod($mock, 'findParamValueWithType');
        $ret = $findParamValueWithType->invokeArgs($mock, $params);

        $this->assertSame($inst, $ret);
    }

    public function test_invokeWithClassInjection() {
        $i = new \PhpInjector\Injector('objectInjectionFunction');
        $obj = new AFancyClassUnderTest();
        $ao = new ArrayObject([1, 2, 3]);
        $zahl = 5.5;
        $ret = $i->invoke([
            'ArrayObject' => $ao,
            'AFancyClassUnderTest' => $obj,
            'zahl' => $zahl,
        ]);
        $exp = [
            'AFancyClassUnderTest' => $obj,
            'zahl' => 5,
            'ArrayObject' => $ao,
        ];

        $this->assertEquals($exp, $ret);
    }

    public function test_SetServiceContainerViaParam() {
        $sc = $this->getMockBuilder('TestServiceContainer')
            ->onlyMethods(['has', 'get'])
            ->getMock();
        $obj = new AFancyClassUnderTest();
        $i = new \PhpInjector\Injector('objectInjectionFunction', ['service_container' => $sc]);
        $this->assertSame($sc, $i->getServiceContainer());
        $this->assertTrue($i->hasServiceContainer());
    }

    public function test_SetServiceContainer() {
        $sc = $this->getMockBuilder('TestServiceContainer')
            ->onlyMethods(['has', 'get'])
            ->getMock();
        $obj = new AFancyClassUnderTest();
        $i = new \PhpInjector\Injector('objectInjectionFunction');
        $i->setServiceContainer($sc);
        $this->assertSame($sc, $i->getServiceContainer());
        $this->assertTrue($i->hasServiceContainer());
    }

    public function test_injectsServiceFromContainer() {
        $i = new \PhpInjector\Injector('objectInjectionFunction');
        $sc = $this->getMockBuilder('TestServiceContainer')
            ->onlyMethods(['has', 'get'])
            ->getMock();
        $obj = new AFancyClassUnderTest();
        $ao = new ArrayObject([1, 2, 3]);
        $zahl = 5.5;
        $exp = [
            'AFancyClassUnderTest' => $obj,
            'zahl' => 5,
            'ArrayObject' => $ao,
        ];

        $sc->method('has')->willReturn(true);
        $sc->method('get')->willReturn($obj);
        $i->setServiceContainer($sc);

        $ret = $i->invoke([
            'ArrayObject' => $ao,
            'zahl' => $zahl,
        ]);

        $this->assertEquals($exp, $ret);
    }

    public function test_injectsServiceFromRealContainer() {
        $i = new \PhpInjector\Injector('objectInjectionFunction');
        $sc = new TestServiceContainer();
        $obj = new AFancyClassUnderTest();
        $ao = new ArrayObject([1, 2, 3]);
        $zahl = 5.5;
        $exp = [
            'AFancyClassUnderTest' => $obj,
            'zahl' => 5,
            'ArrayObject' => $ao,
        ];

        $sc->hasService = true;
        $sc->service = $obj;
        $i->setServiceContainer($sc);

        $ret = $i->invoke([
            'ArrayObject' => $ao,
            'zahl' => $zahl,
        ]);

        $this->assertEquals($exp, $ret);
    }
}
