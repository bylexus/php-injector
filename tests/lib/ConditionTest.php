<?php
class ConditionTest extends \PHPUnit_Framework_TestCase {

	public function test_getConditionForNumbers() {
		$this->assertInstanceOf('\PhpInjector\NumberCondition',\PhpInjector\Condition::getCondition('Int','1..100'));
		$this->assertInstanceOf('\PhpInjector\NumberCondition',\PhpInjector\Condition::getCondition('Integer','1..100'));
		$this->assertInstanceOf('\PhpInjector\NumberCondition',\PhpInjector\Condition::getCondition('Float','1..100'));
		$this->assertInstanceOf('\PhpInjector\NumberCondition',\PhpInjector\Condition::getCondition('Double','1..100'));
	}

	public function test_getConditionForStrings() {
		$this->assertInstanceOf('\PhpInjector\StringCondition',\PhpInjector\Condition::getCondition('String','1..100'));
	}

	public function test_getConditionForTimestamps() {
		$this->assertInstanceOf('\PhpInjector\TimestampCondition',\PhpInjector\Condition::getCondition('Timestamp','>now'));
	}

	public function test_getConditionForUnknown() {
		$this->assertSame(null,\PhpInjector\Condition::getCondition('unknown','1..100'));
	}

}