<?php
/**
 * PhpInjector
 *
 * @author Alexander Schenkel, alex@alexi.ch
 * @copyright 2015 Alexander Schenkel
 * @link https://github.com/bylexus/php-injector
 *
 * released under the MIT license, @link http://opensource.org/licenses/MIT
 */
namespace PhpInjector {
    class TypeCaster {
        public static function cast($value, $type) {
            if ($type instanceof \ReflectionNamedType) {
                $type = $type->getName();
            }
            // strip away my own namespace from type:
            $type = preg_replace('/^\\\\{0,1}' . __NAMESPACE__ . '\\\\/', '', $type);
            switch (strtolower($type)) {
                case 'boolean':
                case 'bool':return BooleanTypeCaster::cast($value);
                case 'integer':
                case 'int':return IntegerTypeCaster::cast($value);
                case 'float':
                case 'double':return FloatTypeCaster::cast($value);
                case 'string':return StringTypeCaster::cast($value);
                case 'array':return ArrayTypeCaster::cast($value);
                case 'object':return ObjectTypeCaster::cast($value);
                case 'mixed':return $value;
                case 'json':return JsonTypeCaster::cast($value);
                case 'timestamp':return TimestampTypeCaster::cast($value);
            }
            throw new \Exception('Cannot (yet) cast to type ' . $type);
        }
    }
}
