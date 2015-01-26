<?php
namespace PhpInjector {
	class FloatTypeCaster {
		public static function cast($value) {
            if ($value === null) return null;
			return (float)$value;
		}
	}
}
