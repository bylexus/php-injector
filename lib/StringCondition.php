<?php
namespace PhpInjector {
	class StringCondition extends Condition {
		protected function getInternalCompareFunctions() {
			return array(
			// matches e.g. '0..100' (no negatives allowed, no decimals):
			'/^(?P<min>\d+)\.\.(?P<max>\d+)/' => function($testValue, $matches) {
					$min = $matches['min'];
					$max = $matches['max'];
					return $min <= mb_strlen($testValue) && $max >= mb_strlen($testValue);
				},

			// matches e.g. '<= 10 (no decimals, no negatives)':
			'/^(?P<op>[<>]=?|<>)\s*(?P<border>\d+)/' => function($testValue, $matches) {
					$op = $matches['op'];
					$border = $matches['border'];
					switch ($op) {
						case '<': return mb_strlen($testValue) < $border;
						case '<=': return mb_strlen($testValue) <= $border;
						case '>': return mb_strlen($testValue) > $border;
						case '>=': return mb_strlen($testValue) >= $border;
						case '<>': return mb_strlen($testValue) != $border;
					}
					return false;
				}
			);
		}
	}
}
