<?php
namespace PhpInjector {
	class ObjectTypeCaster {
		public static function cast($value) {
            if ($value === null) return null;
			return (object)$value;
		}
	}
}
