<?php

use PHPUnit\Framework\TestCase;

function IntegrationTest_simple_function($a, $b, $c = null, $d = 'hello') {
    return array($a, $b, $c, $d);
}

function IntegrationTest_simple_function_with_types(int $a, int $b = 42, ?int $c = 43) {
    return array($a, $b, $c);
}

function IntegrationTest_simple_function_with_bool_types(bool $a, bool $b = true, ?bool $c = false) {
    return array($a, $b, $c);
}

/**
 * @param int $a a number
 * @param string $b a string
 * @param timestamp $c A date
 * @param boolean $d another number
 */
function IntegrationTest_typecasting_function($a, $b, $c = null, $d = true) {
    return array($a, $b, $c, $d);
}

/**
 * @param int[>0] $a a number
 * @param string[5..10] $b a string
 * @param timestamp[>=1.1.2000] $c A date
 * @param boolean $d another number
 */
function IntegrationTest_typecasting_function_with_conditions($a, $b, $c = null, $d = true) {
    return array($a, $b, $c, $d);
}

class IntegrationTest_TestClass {
    public function IntegrationTest_simple_function($a, $b, $c = null, $d = 'hello') {
        return array($a, $b, $c, $d);
    }

    /**
     * @param int $a a number
     * @param string $b a string
     * @param timestamp $c A date
     * @param boolean $d another number
     */
    public function IntegrationTest_typecasting_function($a, $b, $c = null, $d = true) {
        return array($a, $b, $c, $d);
    }

    /**
     * @param int[>0] $a a number
     * @param string[5..10] $b a string
     * @param timestamp[>=1.1.2000] $c A date
     * @param boolean $d another number
     */
    public function IntegrationTest_typecasting_function_with_conditions($a, $b, $c = null, $d = true) {
        return array($a, $b, $c, $d);
    }
}

class InjectorIntegrationTest extends TestCase {

    public function test_simpleFunctionWithMissingParameter() {
        $this->expectException(\Exception::class);
        $inj = new \PhpInjector\Injector('IntegrationTest_simple_function');
        $ret = $inj->invoke(array());
    }

    public function test_simpleFunctionWithOnlyNeededParams() {
        $inj = new \PhpInjector\Injector('IntegrationTest_simple_function');
        $ret = $inj->invoke(array('b' => 'zwei', 'a' => 'eins'));
        $this->assertSame(array('eins', 'zwei', null, 'hello'), $ret);
    }

    public function test_simpleFunctionWithOptionalParams() {
        $inj = new \PhpInjector\Injector('IntegrationTest_simple_function');
        $ret = $inj->invoke(array('b' => 'zwei', 'a' => 'eins', 'd' => 'moins', 'c' => 5));
        $this->assertSame(array('eins', 'zwei', 5, 'moins'), $ret);
    }

    public function test_typecasting_function_with_only_needed_params() {
        $inj = new \PhpInjector\Injector('IntegrationTest_typecasting_function');
        $ret = $inj->invoke(array('b' => 'zwei', 'a' => '8.8'));
        $this->assertSame(array(8, 'zwei', null, true), $ret);
    }

    public function test_typecasting_function_with_null_params() {
        $inj = new \PhpInjector\Injector('IntegrationTest_typecasting_function');
        $ret = $inj->invoke(array('b' => null, 'a' => null, 'd' => null, 'c' => null));
        $this->assertSame(array(null, null, null, null), $ret);
    }

    public function test_typecasting_function_with_params() {
        $inj = new \PhpInjector\Injector('IntegrationTest_typecasting_function');
        $ret = $inj->invoke(array('b' => 1000, 'a' => '1000', 'd' => false, 'c' => '1.1.2000'));
        $this->assertSame(array(1000, '1000', strtotime('2000-01-01'), false), $ret);
    }

    public function test_typecasting_function_with_condition_with_only_needed_params() {
        $inj = new \PhpInjector\Injector('IntegrationTest_typecasting_function_with_conditions');
        $ret = $inj->invoke(array('b' => '12345', 'a' => 1));
        $this->assertSame(array(1, '12345', null, true), $ret);

        $ret = $inj->invoke(array('b' => '1234567890', 'a' => 1000));
        $this->assertSame(array(1000, '1234567890', null, true), $ret);
    }

    public function test_typecasting_function_with_string_a() {
        $this->expectException(\Exception::class);
        $inj = new \PhpInjector\Injector('IntegrationTest_typecasting_function_with_conditions');
        $ret = $inj->invoke(array('b' => 'fuenf', 'a' => 'aaalex'));
    }

    public function test_typecasting_function_with_condition_with_condition_exception() {
        $this->expectException(\Exception::class);
        $inj = new \PhpInjector\Injector('IntegrationTest_typecasting_function_with_conditions');
        $ret = $inj->invoke(array('b' => '1234', 'a' => 0));
    }

    public function test_typecasting_function_with_condition_with_all_params() {
        $inj = new \PhpInjector\Injector('IntegrationTest_typecasting_function_with_conditions');
        $ret = $inj->invoke(array('b' => '12345', 'a' => 1, 'd' => 'false', 'c' => '31.1.2014'));
        $this->assertSame(array(1, '12345', strtotime('2014-01-31'), false), $ret);
    }

    public function test_typecasting_function_with_condition_with_all_params_but_null() {
        $inj = new \PhpInjector\Injector('IntegrationTest_typecasting_function_with_conditions');
        $ret = $inj->invoke(array('b' => '12345', 'a' => 1, 'd' => null, 'c' => null));
        $this->assertSame(array(1, '12345', null, null), $ret);
    }

    public function test_typecasting_function_with_condition_with_all_params_but_wrong() {
        $this->expectException(\Exception::class);
        $inj = new \PhpInjector\Injector('IntegrationTest_typecasting_function_with_conditions');
        $ret = $inj->invoke(array('b' => '12345', 'a' => null, 'd' => null, 'c' => null));
        $this->assertSame(array(1, '12345', null, null), $ret);
    }

    public function test_simpleMethodWithMissingParameter() {
        $this->expectException(\Exception::class);
        $obj = new IntegrationTest_TestClass();
        $inj = new \PhpInjector\Injector(array($obj, 'IntegrationTest_simple_function'));
        $ret = $inj->invoke(array());
    }

    public function test_simpleMethodWithOnlyNeededParams() {
        $obj = new IntegrationTest_TestClass();
        $inj = new \PhpInjector\Injector(array($obj, 'IntegrationTest_simple_function'));
        $ret = $inj->invoke(array('b' => 'zwei', 'a' => 'eins'));
        $this->assertSame(array('eins', 'zwei', null, 'hello'), $ret);
    }

    public function test_simpleMethodWithOptionalParams() {
        $obj = new IntegrationTest_TestClass();
        $inj = new \PhpInjector\Injector(array($obj, 'IntegrationTest_simple_function'));
        $ret = $inj->invoke(array('b' => 'zwei', 'a' => 'eins', 'd' => 'moins', 'c' => 5));
        $this->assertSame(array('eins', 'zwei', 5, 'moins'), $ret);
    }

    public function test_typecasting_method_with_only_needed_params() {
        $obj = new IntegrationTest_TestClass();
        $inj = new \PhpInjector\Injector(array($obj, 'IntegrationTest_typecasting_function'));
        $ret = $inj->invoke(array('b' => 'zwei', 'a' => '8.8'));
        $this->assertSame(array(8, 'zwei', null, true), $ret);
    }

    public function test_typecasting_method_with_null_params() {
        $obj = new IntegrationTest_TestClass();
        $inj = new \PhpInjector\Injector(array($obj, 'IntegrationTest_typecasting_function'));
        $ret = $inj->invoke(array('b' => null, 'a' => null, 'd' => null, 'c' => null));
        $this->assertSame(array(null, null, null, null), $ret);
    }

    public function test_typecasting_method_with_params() {
        $obj = new IntegrationTest_TestClass();
        $inj = new \PhpInjector\Injector(array($obj, 'IntegrationTest_typecasting_function'));
        $ret = $inj->invoke(array('b' => 1000, 'a' => '1000', 'd' => false, 'c' => '1.1.2000'));
        $this->assertSame(array(1000, '1000', strtotime('2000-01-01'), false), $ret);
    }

    public function test_typecasting_method_with_condition_with_only_needed_params() {
        $obj = new IntegrationTest_TestClass();
        $inj = new \PhpInjector\Injector(array($obj, 'IntegrationTest_typecasting_function_with_conditions'));
        $ret = $inj->invoke(array('b' => '12345', 'a' => 1));
        $this->assertSame(array(1, '12345', null, true), $ret);

        $ret = $inj->invoke(array('b' => '1234567890', 'a' => 1000));
        $this->assertSame(array(1000, '1234567890', null, true), $ret);
    }

    public function test_typecasting_method_with_condition_and_wrong_a() {
        $this->expectException(\Exception::class);
        $obj = new IntegrationTest_TestClass();
        $inj = new \PhpInjector\Injector(array($obj, 'IntegrationTest_typecasting_function_with_conditions'));
        $ret = $inj->invoke(array('b' => '1234', 'a' => 'hello'));
    }

    public function test_typecasting_method_with_condition_with_condition_exception() {
        $this->expectException(\Exception::class);
        $obj = new IntegrationTest_TestClass();
        $inj = new \PhpInjector\Injector(array($obj, 'IntegrationTest_typecasting_function_with_conditions'));
        $ret = $inj->invoke(array('b' => '1234', 'a' => 0));
    }

    public function test_typecasting_method_with_condition_with_all_params() {
        $obj = new IntegrationTest_TestClass();
        $inj = new \PhpInjector\Injector(array($obj, 'IntegrationTest_typecasting_function_with_conditions'));
        $ret = $inj->invoke(array('b' => '12345', 'a' => 1, 'd' => 'false', 'c' => '31.1.2014'));
        $this->assertSame(array(1, '12345', strtotime('2014-01-31'), false), $ret);
    }

    public function test_typecasting_method_with_condition_with_all_params_but_null() {
        $obj = new IntegrationTest_TestClass();
        $inj = new \PhpInjector\Injector(array($obj, 'IntegrationTest_typecasting_function_with_conditions'));
        $ret = $inj->invoke(array('b' => '12345', 'a' => 1, 'd' => null, 'c' => null));
        $this->assertSame(array(1, '12345', null, null), $ret);
    }

    public function test_typecasting_method_with_condition_with_all_params_but_wrong() {
        $this->expectException(\Exception::class);
        $obj = new IntegrationTest_TestClass();
        $inj = new \PhpInjector\Injector(array($obj, 'IntegrationTest_typecasting_function_with_conditions'));
        $ret = $inj->invoke(array('b' => '12345', 'a' => null, 'd' => null, 'c' => null));
        $this->assertSame(array(1, '12345', null, null), $ret);
    }


    public function test_simpleFunctionWithTypeCasting() {
        $values = ['a' => '10'];
        $inj = new \PhpInjector\Injector('IntegrationTest_simple_function_with_types');
        $ret = $inj->invoke($values);
        $this->assertSame(array(10, 42, 43), $ret);

        $values = ['a' => '10', 'b' => 20, 'c' => null];
        $inj = new \PhpInjector\Injector('IntegrationTest_simple_function_with_types');
        $ret = $inj->invoke($values);
        $this->assertSame(array(10, 20, null), $ret);

        $values = ['a' => '10', 'b' => '', 'c' => 3.5];
        $inj = new \PhpInjector\Injector('IntegrationTest_simple_function_with_types');
        $ret = $inj->invoke($values);
        $this->assertSame(array(10, 0, 3), $ret);

        $values = ['a' => true, 'b' => 3.5, 'c' => ''];
        $inj = new \PhpInjector\Injector('IntegrationTest_simple_function_with_types');
        $ret = $inj->invoke($values);
        $this->assertSame(array(1, 3, 0), $ret);
    }

    public function test_simpleFunctionWithNonNullableTypeException() {
        $this->expectException(\TypeError::class);
        // $b is not nullable, but we pass null. This must result in a type error, because the null value is
        // not acceptable for the type int $b = 42:
        $values = ['a' => '10', 'b' => null];
        $inj = new \PhpInjector\Injector('IntegrationTest_simple_function_with_types');
        $inj->invoke($values);
    }

    public function test_simpleFunctionWithBoolTypeCasting() {
        $values = ['a' => 'true'];
        $inj = new \PhpInjector\Injector('IntegrationTest_simple_function_with_bool_types');
        $ret = $inj->invoke($values);
        $this->assertSame(array(true, true, false), $ret);

        $values = ['a' => 'true', 'b' => false, 'c' => null];
        $inj = new \PhpInjector\Injector('IntegrationTest_simple_function_with_bool_types');
        $ret = $inj->invoke($values);
        $this->assertSame(array(true, false, null), $ret);

        $values = ['a' => 'true', 'b' => '', 'c' => 3.5];
        $inj = new \PhpInjector\Injector('IntegrationTest_simple_function_with_bool_types');
        $ret = $inj->invoke($values);
        $this->assertSame(array(true, false, false), $ret);

        $values = ['a' => true, 'b' => 3.5, 'c' => ''];
        $inj = new \PhpInjector\Injector('IntegrationTest_simple_function_with_bool_types');
        $ret = $inj->invoke($values);
        $this->assertSame(array(true, false, false), $ret);
    }

    public function test_simpleFunctionWithNonNullableBoolTypeException() {
        $this->expectException(\TypeError::class);
        // $b is not nullable, but we pass null. This must result in a type error, because the null value is
        // not acceptable for the type int $b = 42:
        $values = ['a' => '10', 'b' => null];
        $inj = new \PhpInjector\Injector('IntegrationTest_simple_function_with_bool_types');
        $inj->invoke($values);
    }
}
