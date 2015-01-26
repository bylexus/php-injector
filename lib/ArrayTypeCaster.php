<?php
namespace PhpInjector {
	class ArrayTypeCaster {
		public static function cast($value) {
            if ($value === null) return null;
			return (array)$value;
		}
	}
}
