<?php
use PHPUnit\Framework\TestCase;

class StringConditionTest extends TestCase
{

    public function setUp()
    {
        mb_internal_encoding("UTF-8");
    }

    public function constructDataProvider()
    {
        return array(
            array(' 0..100'),
            array(' 2..100'),
            array('<20'),
            array(' <= 20 '),
            array('>  20'),
            array('>=20'),
        );
    }

    public function constructFailDataProvider()
    {
        return array(
            array(null),
            array(''),
            array('   '),
        );
    }

    /**
     * @dataProvider constructDataProvider
     */
    public function test_Construct($condition)
    {
        $i = new \PhpInjector\StringCondition($condition);
        $this->assertInstanceOf(\PhpInjector\StringCondition::class, $i);
    }

    /**
     * @expectedException Exception
     * @dataProvider constructFailDataProvider
     */
    public function test_ConstuctWithError($condition)
    {
        new \PhpInjector\StringCondition($condition);
    }

    public function test_check_range()
    {
        $c = new \PhpInjector\StringCondition(' 1..10');

        $this->assertTrue($c->check('1'));
        $this->assertTrue($c->check('ä'));
        $this->assertTrue($c->check('12345'));
        $this->assertTrue($c->check('1234567890'));
        $this->assertTrue($c->check('123456789ä'));

        $this->assertFalse($c->check(''));
        $this->assertFalse($c->check('12345678901'));
    }

    public function test_check_operator()
    {
        $c = new \PhpInjector\StringCondition('> 2');
        $this->assertTrue($c->check('123'));
        $this->assertTrue($c->check('12ä'));
        $this->assertTrue($c->check('123456'));
        $this->assertFalse($c->check('12'));
        $this->assertFalse($c->check('1ä'));

        $c = new \PhpInjector\StringCondition('< 2');
        $this->assertTrue($c->check('1'));
        $this->assertTrue($c->check(''));
        $this->assertTrue($c->check('ä'));
        $this->assertFalse($c->check('11'));
        $this->assertFalse($c->check('111'));
        $this->assertFalse($c->check('1ä'));

        $c = new \PhpInjector\StringCondition('<= 2');
        $this->assertTrue($c->check('1'));
        $this->assertTrue($c->check('11'));
        $this->assertTrue($c->check('1ä'));
        $this->assertTrue($c->check(''));
        $this->assertFalse($c->check('111'));
        $this->assertFalse($c->check('1111'));
        $this->assertFalse($c->check('11ä'));

        $c = new \PhpInjector\StringCondition('>= 2');
        $this->assertTrue($c->check('12'));
        $this->assertTrue($c->check('1ä'));
        $this->assertTrue($c->check('123'));
        $this->assertTrue($c->check('12ä'));
        $this->assertTrue($c->check('123456'));
        $this->assertFalse($c->check('1'));
        $this->assertFalse($c->check('ä'));
        $this->assertFalse($c->check(''));

        $c = new \PhpInjector\StringCondition('<> 3');
        $this->assertTrue($c->check(''));
        $this->assertTrue($c->check('1'));
        $this->assertTrue($c->check('12'));
        $this->assertTrue($c->check('123ä'));
        $this->assertTrue($c->check('1234'));
        $this->assertFalse($c->check('123'));
        $this->assertFalse($c->check('öäü'));
    }

    public function test_check_wordlist()
    {
        $c = new \PhpInjector\StringCondition('word1| WORD 2 |word\|3');
        $this->assertTrue($c->check('wOrD1'));
        $this->assertTrue($c->check(' word 2 '));
        $this->assertTrue($c->check('word|3'));
        $this->assertFalse($c->check(' word1'));
        $this->assertFalse($c->check('another'));
        $this->assertFalse($c->check('word|5'));
    }
}
