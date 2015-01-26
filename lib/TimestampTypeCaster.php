<?php
namespace PhpInjector {
	class TimestampTypeCaster {
		public static function cast($value) {
            if ($value === null) return null;
			return @strtotime($value);
		}
	}
}
