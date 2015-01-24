<?php
namespace PhpInjector {
	class TypeCaster {
		public static function cast($value, $type) {
			// strip away my own namespace from type:			
			$type = preg_replace('/^\\\\{0,1}'.__NAMESPACE__.'\\\\/', '', $type);
			switch (strtolower($type)) {
				case 'boolean':
				case 'bool': return BooleanTypeCaster::cast($value);
				case 'integer':
				case 'int': return IntegerTypeCaster::cast($value);
				case 'float':
				case 'double': return FloatTypeCaster::cast($value);
				case 'string': return StringTypeCaster::cast($value);
				case 'array': return ArrayTypeCaster::cast($value);
				case 'object': return ObjectTypeCaster::cast($value);
				case 'mixed': return $value;
				case 'json': return JsonTypeCaster::cast($value);
				case 'timestamp': return TimestampTypeCaster::cast($value);
			}
			throw new \Exception('Cannot (yet) cast to type '.$type);
		}
	}	
}
