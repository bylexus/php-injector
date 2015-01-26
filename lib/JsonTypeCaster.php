<?php
namespace PhpInjector {
	class JsonTypeCaster {
		public static function cast($value) {
            if ($value === null) return null;
			return @json_decode($value);
		}
	}
}
