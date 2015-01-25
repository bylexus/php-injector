<?php
namespace PhpInjector {
	class TimestampCondition extends Condition {
		protected function getInternalCompareFunctions() {
			return array(
			// matches e.g. '1.1.2000..2014/12/22' (or, better, almost everything that strtotime can handle):
			'/^(?P<min>.+(?<!\.))\.\.(?P<max>[^\.]+)/' => function($testValue, $matches) {
					$min = strtotime($matches['min']);
					$max = strtotime($matches['max']);
					$testValue = strtotime($testValue);
					if ($min === false || $max === false) {
						throw new \Exception('Wrong date / time format for condition');
					}
					return $min <= $testValue && $max >= $testValue;
				},

			// matches e.g. '<= 1.1.2000, or everything that strtotime can handle':
			'/^(?P<op>[<>]=?|<>)\s*(?P<border>[^<>]+)/' => function($testValue, $matches) {
					$op = $matches['op'];
					$border = strtotime($matches['border']);
					$testValue = strtotime($testValue);
					if ($border === false) {
						throw new \Exception('Wrong date / time format for condition');
					}
					switch ($op) {
						case '<': return $testValue < $border;
						case '<=': return $testValue <= $border;
						case '>': return $testValue > $border;
						case '>=': return $testValue >= $border;
						case '<>': return $testValue != $border;
					}
					return false;
				}
			);
		}
	}
}
