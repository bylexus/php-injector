<?php
namespace PhpInjector {
	class StringTypeCaster {
		public static function cast($value) {
            if ($value === null) return null;
			return (string)$value;
		}
	}
}
