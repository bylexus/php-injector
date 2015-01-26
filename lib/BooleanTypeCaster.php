<?php
namespace PhpInjector {
	class BooleanTypeCaster {
		public static function cast($value) {
            if ($value === null) return null;
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
