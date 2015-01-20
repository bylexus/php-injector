<?php
namespace PhpInjector {
	class FloatTypeCaster {
		public static function cast($value) {
			return (float)$value;
		}
	}	
}
