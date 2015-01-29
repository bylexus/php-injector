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
    /**
     * Implements condition checks for String types.
     */
	class StringCondition extends Condition {
		protected function getInternalCompareFunctions() {
			return array(
			// matches e.g. '0..100' (no negatives allowed, no decimals):
			'/^(?P<min>\d+)\.\.(?P<max>\d+)/' => function($testValue, $matches) {
				$min = $matches['min'][0];
				$max = $matches['max'][0];
				return $min <= mb_strlen($testValue) && $max >= mb_strlen($testValue);
			},

			// matches e.g. '<= 10 (no decimals, no negatives)':
			'/^(?P<op>[<>]=?|<>)\s*(?P<border>\d+)/' => function($testValue, $matches) {
				$op = $matches['op'][0];
				$border = $matches['border'][0];
				switch ($op) {
					case '<': return mb_strlen($testValue) < $border;
					case '<=': return mb_strlen($testValue) <= $border;
					case '>': return mb_strlen($testValue) > $border;
					case '>=': return mb_strlen($testValue) >= $border;
					case '<>': return mb_strlen($testValue) != $border;
				}
				return false;
			},

            // matches e.g. 'word1|word2|word with\|pipe':
            '/(?P<w>(?:[^\\\\|]+|\\\\\\|?)+)/' => function($testValue, $matches) {
                $check = array_map(function($el) {
                    return mb_strtolower(str_replace('\|', '|', $el));
                }, $matches['w']);
                return in_array(mb_strtolower($testValue), $check);
            });
		}
	}
}
