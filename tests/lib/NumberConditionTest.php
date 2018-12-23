<?php
use PHPUnit\Framework\TestCase;

class NumberConditionTest extends TestCase
{

    public function test_Construct()
    {
        $i = new \PhpInjector\NumberCondition(' -5.51..100');
        $this->assertInstanceOf(\PhpInjector\NumberCondition::class, $i);
        $i = new \PhpInjector\NumberCondition('<-1.0');
        $this->assertInstanceOf(\PhpInjector\NumberCondition::class, $i);
        $i = new \PhpInjector\NumberCondition(' <= 1.0 ');
        $this->assertInstanceOf(\PhpInjector\NumberCondition::class, $i);
        $i = new \PhpInjector\NumberCondition('>  -1.0');
        $this->assertInstanceOf(\PhpInjector\NumberCondition::class, $i);
        $i = new \PhpInjector\NumberCondition('>=1.0');
        $this->assertInstanceOf(\PhpInjector\NumberCondition::class, $i);
    }

    /**
     * @expectedException Exception
     */
    public function test_ConstuctWithError()
    {
        new \PhpInjector\NumberCondition(' -5.51...100');
    }

    public function test_check_range()
    {
        $c = new \PhpInjector\NumberCondition(' -5.51..100.25');

        $this->assertTrue($c->check(-5.51));
        $this->assertTrue($c->check(0));
        $this->assertTrue($c->check(50));
        $this->assertTrue($c->check(100.25));

        $this->assertFalse($c->check(-5.52));
        $this->assertFalse($c->check(101));
    }

    public function test_check_operator()
    {
        $c = new \PhpInjector\NumberCondition('> -2.5');
        $this->assertTrue($c->check(-2));
        $this->assertTrue($c->check(2));
        $this->assertTrue($c->check(0));
        $this->assertFalse($c->check(-3));

        $c = new \PhpInjector\NumberCondition('< 2.5');
        $this->assertTrue($c->check(-2));
        $this->assertTrue($c->check(2));
        $this->assertTrue($c->check(0));
        $this->assertFalse($c->check(3));

        $c = new \PhpInjector\NumberCondition('<= 2.5');
        $this->assertTrue($c->check(-2));
        $this->assertTrue($c->check(2));
        $this->assertTrue($c->check(0));
        $this->assertTrue($c->check(2.5));
        $this->assertFalse($c->check(3));

        $c = new \PhpInjector\NumberCondition('>= -2.5');
        $this->assertTrue($c->check(-2.5));
        $this->assertTrue($c->check(-2));
        $this->assertTrue($c->check(2));
        $this->assertTrue($c->check(0));
        $this->assertTrue($c->check(2.5));
        $this->assertFalse($c->check(-2.6));

        $c = new \PhpInjector\NumberCondition('<> -2.5');
        $this->assertFalse($c->check(-2.5));
        $this->assertTrue($c->check(-2.6));
        $this->assertTrue($c->check(-2.4));

        $c = new \PhpInjector\NumberCondition('>0');
        $this->assertFalse($c->check(0));
        $this->assertFalse($c->check(-2.6));
        $this->assertTrue($c->check(1));

        $this->assertFalse($c->check('abc'));
    }
}
