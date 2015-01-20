<?php
namespace PhpInjector {
	class ObjectTypeCaster {
		public static function cast($value) {
			return (object)$value;
		}
	}	
}
