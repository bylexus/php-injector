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
	class NumberCondition extends Condition {
		protected function getInternalCompareFunctions() {
			return array(
				// matches e.g. '-1.0..100':
				'/^(?P<min>-?\d+(\.\d+)*)\.\.(?P<max>-?\d+(\.\d+)*)/' => function ($testValue, $matches) {
					$testValue = (float)$testValue;
					$min = (float)$matches['min'][0];
					$max = (float)$matches['max'][0];
					return $min <= $testValue && $max >= $testValue;
				},

				// matches e.g. '<= -1':
				'/^(?P<op>[<>]=?|<>)\s*(?P<border>-?\d+(\.\d+)*)/' => function ($testValue, $matches) {
					$testValue = (float)$testValue;
					$op = $matches['op'][0];
					$border = (float)$matches['border'][0];
					switch ($op) {
						case '<':
							return $testValue < $border;
						case '<=':
							return $testValue <= $border;
						case '>':
							return $testValue > $border;
						case '>=':
							return $testValue >= $border;
						case '<>':
							return $testValue != $border;
					}
					return false;
				}
			);
		}
	}
}
