<?php

/**
 * PhpInjector
 *
 * @author Alexander Schenkel, alex@alexi.ch
 * @copyright 2015-2025 Alexander Schenkel
 * @link https://github.com/bylexus/php-injector
 *
 * released under the MIT license, @link http://opensource.org/licenses/MIT
 */

namespace PhpInjector {
	class BooleanTypeCaster {
		public static function cast($value) {
			if ($value === null) {
				return null;
			}
			return
				true === $value ||
				$value == 1 ||
				strtolower($value) === 'true' ||
				strtolower($value) === 'on' ||
				strtolower($value) === 'yes' ||
				strtolower($value) === 't' ||
				strtolower($value) === 'true';
		}
	}
}
