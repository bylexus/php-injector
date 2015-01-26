<?php
namespace PhpInjector {
	class IntegerTypeCaster {
		public static function cast($value) {
            if ($value === null) return null;
			return (int)$value;
		}
	}
}
