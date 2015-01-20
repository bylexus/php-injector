<?php
namespace PhpInjector {
	class StringTypeCaster {
		public static function cast($value) {
			return (string)$value;
		}
	}	
}
