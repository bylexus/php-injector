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
	class StringTypeCaster {
		public static function cast($value) {
            if ($value === null) return null;
			return (string)$value;
		}
	}
}
