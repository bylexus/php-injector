<?php

function IntegrationTest_simple_function($a, $b, $c = null, $d = 'hello') {
    return array($a, $b,$c,$d);
}

/**
 * @param int $a a number
 * @param string $b a string
 * @param timestamp $c A date
 * @param boolean $d another number
 */
function IntegrationTest_typecasting_function($a, $b, $c = null, $d = true) {
    return array($a, $b,$c,$d);
}

/**
 * @param int[>0] $a a number
 * @param string[5..10] $b a string
 * @param timestamp[>=1.1.2000] $c A date
 * @param boolean $d another number
 */
function IntegrationTest_typecasting_function_with_conditions($a, $b, $c = null, $d = true) {
    return array($a, $b,$c,$d);
}

class IntegrationTest_TestClass {
    public function IntegrationTest_simple_function($a, $b, $c = null, $d = 'hello') {
        return array($a, $b,$c,$d);
    }

    /**
     * @param int $a a number
     * @param string $b a string
     * @param timestamp $c A date
     * @param boolean $d another number
     */
    public function IntegrationTest_typecasting_function($a, $b, $c = null, $d = true) {
        return array($a, $b,$c,$d);
    }

    /**
     * @param int[>0] $a a number
     * @param string[5..10] $b a string
     * @param timestamp[>=1.1.2000] $c A date
     * @param boolean $d another number
     */
    public function IntegrationTest_typecasting_function_with_conditions($a, $b, $c = null, $d = true) {
        return array($a, $b,$c,$d);
    }
}

class InjectorIntegrationTest extends \PHPUnit_Framework_TestCase {

    /**
     * @expectedException Exception
     */
    public function test_simpleFunctionWithMissingParameter() {
        $inj = new \PhpInjector\Injector('IntegrationTest_simple_function');
        $ret = $inj->invoke(array());
    }

    public function test_simpleFunctionWithOnlyNeededParams() {
        $inj = new \PhpInjector\Injector('IntegrationTest_simple_function');
        $ret = $inj->invoke(array('b'=>'zwei', 'a' => 'eins'));
        $this->assertSame(array('eins','zwei',null,'hello'),$ret);
    }

    public function test_simpleFunctionWithOptionalParams() {
        $inj = new \PhpInjector\Injector('IntegrationTest_simple_function');
        $ret = $inj->invoke(array('b'=>'zwei', 'a' => 'eins', 'd' => 'moins', 'c' => 5));
        $this->assertSame(array('eins','zwei',5,'moins'),$ret);
    }



    public function test_typecasting_function_with_only_needed_params() {
        $inj = new \PhpInjector\Injector('IntegrationTest_typecasting_function');
        $ret = $inj->invoke(array('b'=>'zwei', 'a' => '8.8'));
        $this->assertSame(array(8,'zwei',null,true),$ret);
    }

    public function test_typecasting_function_with_null_params() {
        $inj = new \PhpInjector\Injector('IntegrationTest_typecasting_function');
        $ret = $inj->invoke(array('b'=>null, 'a' => null,'d'=>null,'c'=>null));
        $this->assertSame(array(null,null,null,null),$ret);
    }

    public function test_typecasting_function_with_params() {
        $inj = new \PhpInjector\Injector('IntegrationTest_typecasting_function');
        $ret = $inj->invoke(array('b'=>1000, 'a' => '1000','d'=>false,'c'=>'1.1.2000'));
        $this->assertSame(array(1000,'1000',strtotime('2000-01-01'),false),$ret);
    }



    public function test_typecasting_function_with_condition_with_only_needed_params() {
        $inj = new \PhpInjector\Injector('IntegrationTest_typecasting_function_with_conditions');
        $ret = $inj->invoke(array('b'=>'12345', 'a' => 1));
        $this->assertSame(array(1,'12345',null,true),$ret);

        $ret = $inj->invoke(array('b'=>'1234567890', 'a' => 1000));
        $this->assertSame(array(1000,'1234567890',null,true),$ret);
    }

    /**
     * @expectedException Exception
     */
    public function test_typecasting_function_with_condition_with_condition_exception() {
        $inj = new \PhpInjector\Injector('IntegrationTest_typecasting_function_with_conditions');
        $ret = $inj->invoke(array('b'=>'1234', 'a' => 0));
    }

    public function test_typecasting_function_with_condition_with_all_params() {
        $inj = new \PhpInjector\Injector('IntegrationTest_typecasting_function_with_conditions');
        $ret = $inj->invoke(array('b'=>'12345', 'a' => 1,'d'=>'false','c'=>'31.1.2014'));
        $this->assertSame(array(1,'12345',strtotime('2014-01-31'),false),$ret);
    }

    public function test_typecasting_function_with_condition_with_all_params_but_null() {
        $inj = new \PhpInjector\Injector('IntegrationTest_typecasting_function_with_conditions');
        $ret = $inj->invoke(array('b'=>'12345', 'a' => 1,'d'=>null,'c'=>null));
        $this->assertSame(array(1,'12345',null,null),$ret);
    }

    /**
     * @expectedException Exception
     */
    public function test_typecasting_function_with_condition_with_all_params_but_wrong() {
        $inj = new \PhpInjector\Injector('IntegrationTest_typecasting_function_with_conditions');
        $ret = $inj->invoke(array('b'=>'12345', 'a' => null,'d'=>null,'c'=>null));
        $this->assertSame(array(1,'12345',null,null),$ret);
    }













    /**
     * @expectedException Exception
     */
    public function test_simpleMethodWithMissingParameter() {
        $obj = new IntegrationTest_TestClass();
        $inj = new \PhpInjector\Injector(array($obj,'IntegrationTest_simple_function'));
        $ret = $inj->invoke(array());
    }

    public function test_simpleMethodWithOnlyNeededParams() {
        $obj = new IntegrationTest_TestClass();
        $inj = new \PhpInjector\Injector(array($obj,'IntegrationTest_simple_function'));
        $ret = $inj->invoke(array('b'=>'zwei', 'a' => 'eins'));
        $this->assertSame(array('eins','zwei',null,'hello'),$ret);
    }

    public function test_simpleMethodWithOptionalParams() {
        $obj = new IntegrationTest_TestClass();
        $inj = new \PhpInjector\Injector(array($obj,'IntegrationTest_simple_function'));
        $ret = $inj->invoke(array('b'=>'zwei', 'a' => 'eins', 'd' => 'moins', 'c' => 5));
        $this->assertSame(array('eins','zwei',5,'moins'),$ret);
    }



    public function test_typecasting_method_with_only_needed_params() {
        $obj = new IntegrationTest_TestClass();
        $inj = new \PhpInjector\Injector(array($obj,'IntegrationTest_typecasting_function'));
        $ret = $inj->invoke(array('b'=>'zwei', 'a' => '8.8'));
        $this->assertSame(array(8,'zwei',null,true),$ret);
    }

    public function test_typecasting_method_with_null_params() {
        $obj = new IntegrationTest_TestClass();
        $inj = new \PhpInjector\Injector(array($obj,'IntegrationTest_typecasting_function'));
        $ret = $inj->invoke(array('b'=>null, 'a' => null,'d'=>null,'c'=>null));
        $this->assertSame(array(null,null,null,null),$ret);
    }

    public function test_typecasting_method_with_params() {
        $obj = new IntegrationTest_TestClass();
        $inj = new \PhpInjector\Injector(array($obj,'IntegrationTest_typecasting_function'));
        $ret = $inj->invoke(array('b'=>1000, 'a' => '1000','d'=>false,'c'=>'1.1.2000'));
        $this->assertSame(array(1000,'1000',strtotime('2000-01-01'),false),$ret);
    }



    public function test_typecasting_method_with_condition_with_only_needed_params() {
        $obj = new IntegrationTest_TestClass();
        $inj = new \PhpInjector\Injector(array($obj,'IntegrationTest_typecasting_function_with_conditions'));
        $ret = $inj->invoke(array('b'=>'12345', 'a' => 1));
        $this->assertSame(array(1,'12345',null,true),$ret);

        $ret = $inj->invoke(array('b'=>'1234567890', 'a' => 1000));
        $this->assertSame(array(1000,'1234567890',null,true),$ret);
    }

    /**
     * @expectedException Exception
     */
    public function test_typecasting_method_with_condition_with_condition_exception() {
        $obj = new IntegrationTest_TestClass();
        $inj = new \PhpInjector\Injector(array($obj,'IntegrationTest_typecasting_function_with_conditions'));
        $ret = $inj->invoke(array('b'=>'1234', 'a' => 0));
    }

    public function test_typecasting_method_with_condition_with_all_params() {
        $obj = new IntegrationTest_TestClass();
        $inj = new \PhpInjector\Injector(array($obj,'IntegrationTest_typecasting_function_with_conditions'));
        $ret = $inj->invoke(array('b'=>'12345', 'a' => 1,'d'=>'false','c'=>'31.1.2014'));
        $this->assertSame(array(1,'12345',strtotime('2014-01-31'),false),$ret);
    }

    public function test_typecasting_method_with_condition_with_all_params_but_null() {
        $obj = new IntegrationTest_TestClass();
        $inj = new \PhpInjector\Injector(array($obj,'IntegrationTest_typecasting_function_with_conditions'));
        $ret = $inj->invoke(array('b'=>'12345', 'a' => 1,'d'=>null,'c'=>null));
        $this->assertSame(array(1,'12345',null,null),$ret);
    }

    /**
     * @expectedException Exception
     */
    public function test_typecasting_method_with_condition_with_all_params_but_wrong() {
        $obj = new IntegrationTest_TestClass();
        $inj = new \PhpInjector\Injector(array($obj,'IntegrationTest_typecasting_function_with_conditions'));
        $ret = $inj->invoke(array('b'=>'12345', 'a' => null,'d'=>null,'c'=>null));
        $this->assertSame(array(1,'12345',null,null),$ret);
    }
}
