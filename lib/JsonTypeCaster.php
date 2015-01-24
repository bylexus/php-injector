<?php
namespace PhpInjector {
	class JsonTypeCaster {
		public static function cast($value) {
			return @json_decode($value);
		}
	}	
}
