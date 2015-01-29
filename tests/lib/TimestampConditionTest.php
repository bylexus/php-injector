<?php
class TimestampConditionTest extends \PHPUnit_Framework_TestCase {

	public function test_Construct() {
		new \PhpInjector\TimestampCondition(' 1.1.2000..2014/01/01');
		new \PhpInjector\TimestampCondition(' >= 20140101');
	}

	public function test_check_range() {
		$c = new \PhpInjector\TimestampCondition(' 1.1.2000..31.12.2000');

		$this->assertTrue($c->check('2000-01-01'));
		$this->assertTrue($c->check('12/31/2000'));

		$this->assertFalse($c->check(0));
		$this->assertFalse($c->check('31.12.1999'));
		$this->assertFalse($c->check('1.1.2001'));
	}

	public function test_check_operator() {
		$c = new \PhpInjector\TimestampCondition('> 2000-01-01');
		$this->assertTrue($c->check('20000102'));
		$this->assertTrue($c->check('2000-01-02'));
		$this->assertTrue($c->check('now'));
		$this->assertFalse($c->check('19991231'));

		$c = new \PhpInjector\TimestampCondition(' < 2000-01-01');
		$this->assertTrue($c->check('19991231'));
		$this->assertTrue($c->check('12/31/1999'));
		$this->assertTrue($c->check('-2000 years'));
		$this->assertFalse($c->check('20000101'));

		$c = new \PhpInjector\TimestampCondition(' <= 2000-01-01');
		$this->assertTrue($c->check('1.1.2000'));
		$this->assertTrue($c->check('19991231'));
		$this->assertTrue($c->check('12/31/1999'));
		$this->assertTrue($c->check('-2000 years'));
		$this->assertFalse($c->check('20000102'));

		$c = new \PhpInjector\TimestampCondition('>= 2000-01-01');
		$this->assertTrue($c->check('20000102'));
		$this->assertTrue($c->check('20000101'));
		$this->assertTrue($c->check('2000-01-02'));
		$this->assertTrue($c->check('now'));
		$this->assertFalse($c->check('19991231'));

		$c = new \PhpInjector\TimestampCondition('<> 2000-01-01');
		$this->assertTrue($c->check('2000-01-02'));
		$this->assertTrue($c->check('1999-12-31'));
		$this->assertFalse($c->check('20000101'));
	}
}
