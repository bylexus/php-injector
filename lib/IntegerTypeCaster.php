<?php
namespace PhpInjector {
	class IntegerTypeCaster {
		public static function cast($value) {
			return (int)$value;
		}
	}	
}
