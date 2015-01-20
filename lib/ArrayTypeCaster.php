<?php
namespace PhpInjector {
	class ArrayTypeCaster {
		public static function cast($value) {
			return (array)$value;
		}
	}	
}
