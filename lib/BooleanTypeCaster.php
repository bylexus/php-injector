<?php
namespace PhpInjector {
	class BooleanTypeCaster {
		public static function cast($value) {
			return 
				true === $value ||
				$value > 0 ||
				strtolower($value) === 'true' ||
				strtolower($value) === 'on' ||
				strtolower($value) === 'yes' ||
				strtolower($value) === 't' ||
				strtolower($value) === 'true';
		}
	}	
}