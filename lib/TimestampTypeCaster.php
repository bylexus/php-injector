<?php
namespace PhpInjector {
	class TimestampTypeCaster {
		public static function cast($value) {
			return @strtotime($value);
		}
	}	
}
