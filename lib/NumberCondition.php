<?php
namespace PhpInjector {
	class NumberCondition extends Condition {
		protected function getInternalCompareFunctions() {
			return array(
			// matches e.g. '-1.0..100':
			'/^(?P<min>-?\d+(\.\d+)*)\.\.(?P<max>-?\d+(\.\d+)*)/' => function($testValue, $matches) {
					$min = $matches['min'];
					$max = $matches['max'];
					return $min <= $testValue && $max >= $testValue;
				},

			// matches e.g. '<= -1':
			'/^(?P<op>[<>]=?|<>)\s*(?P<border>-?\d+(\.\d+)*)/' => function($testValue, $matches) {
					$op = $matches['op'];
					$border = $matches['border'];
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
