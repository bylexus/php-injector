<?php
/**
 * PhpInjector
 *
 * @author Alexander Schenkel, alex@alexi.ch
 * @copyright 2015 Alexander Schenkel
 * @link https://github.com/bylexus/php-injector
 *
 * released under the MIT license, @link http://opensource.org/licenses/MIT
 */
namespace PhpInjector {
	class TimestampCondition extends Condition {
		protected function getInternalCompareFunctions() {
			return array(
			// matches e.g. '1.1.2000..2014/12/22' (or, better, almost everything that strtotime can handle):
			'/^(?P<min>.+(?<!\.))\.\.(?P<max>.+)/' => function($testValue, $matches) {
					$min = strtotime($matches['min'][0]);
					$max = strtotime($matches['max'][0]);
					$testValue = strtotime($testValue);
					if ($min === false || $max === false) {
						throw new \Exception('Wrong date / time format for condition');
					}
					return $min <= $testValue && $max >= $testValue;
				},

			// matches e.g. '<= 1.1.2000, or everything that strtotime can handle':
			'/^(?P<op>[<>]=?|<>)\s*(?P<border>[^<>]+)/' => function($testValue, $matches) {
					$op = $matches['op'][0];
					$border = strtotime($matches['border'][0]);
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
