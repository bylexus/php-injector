<?php
/**
 * PhpInjector
 *
 * @author Alexander Schenkel, alex@alexi.ch
 * @copyright 2015-2020 Alexander Schenkel
 * @link https://github.com/bylexus/php-injector
 *
 * released under the MIT license, @link http://opensource.org/licenses/MIT
 */

namespace PhpInjector {

    use Psr\Container\ContainerInterface;

    /**
     * The PhpInjector class allows the user to call functions / methods and inject
     * parameters via associative array. It also allows the developer to force parameter
     * type casting and parameter condition checks.
     *
     * See the readme on @link https://github.com/bylexus/php-injector Github for examples and usage.
     */
    class Injector {
        const TYPE_FUNC = 'function';
        const TYPE_METHOD = 'method';

        protected $_object = null;
        protected $_function = null;
        protected $_type = null;
        protected $_reflectionFunction = null;
        protected $_parameters = array();
        protected $_serviceContainer = null;

        public $allowUnknownParams = true;

        /**
         * @param string/array A name of a function (string, e.g 'myFunc') or an
         *           array with an object / method (e.g array($myObj, 'myMethod'))
         *           to create the injector from
         * @param array $options (TBD)
         */
        public function __construct($functionOrMethod, array $options = null) {
            $this->initOptions($options);
            if (is_string($functionOrMethod)) {
                $this->initFunction($functionOrMethod);
                $this->_reflectionFunction = $this->buildFunctionReflector($this->_function);
            } elseif ($functionOrMethod instanceof \Closure) {
                $this->initClosure($functionOrMethod);
                $this->_reflectionFunction = $this->buildFunctionReflector($this->_function);
            } elseif (is_array($functionOrMethod)) {
                $this->initMethod($functionOrMethod);
                $this->_reflectionFunction = $this->buildMethodReflector($this->_object, $this->_function);
            } else {
                throw new \Exception('string or array needed in constructor.');
            }

            $this->_parameters = $this->parseFunctionParams($this->_reflectionFunction->getParameters());
        }

        /**
         * Returns the instance of the ReflectionFunction / ReflectionMethod reflection class
         * instantiated during the construction.
         *
         * @return \ReflectionFunctionAbstract
         */
        public function getReflectionFunction() {
            return $this->_reflectionFunction;
        }

        /**
         * Returns the name of the function to be injected
         *
         * @return string
         */
        public function getFunction() {
            return $this->_function;
        }

        /**
         * Returns the name of the object method to be injected
         *
         * @return string
         */
        public function getObject() {
            return $this->_object;
        }

        /**
         * Known options:
         * - allow_unknown_params: boolean: True to allow parameters not required in the method's signature
         * - service_container: Psr\Container\ContainerInterface A service container to resolve params / Services
         */
        protected function initOptions(array $options = null) {
            if (isset($options['allow_unknown_params'])) {
                $this->allowUnknownParams = BooleanTypeCaster::cast($options['allow_unknown_params']);
            }
            if (isset($options['service_container'])) {
                $this->setServiceContainer($options['service_container']);
            }
        }

        public function setServiceContainer(ContainerInterface $container) {
            $this->_serviceContainer = $container;
        }

        public function getServiceContainer() {
            return $this->_serviceContainer;
        }

        public function hasServiceContainer() {
            return $this->_serviceContainer instanceof ContainerInterface;
        }

        protected function initFunction($funcName) {
            if (function_exists($funcName)) {
                $this->_function = $funcName;
            } else {
                throw new \Exception('Function not found: ' . $funcName);
            }
            return $this->_function;
        }
        protected function initClosure(\Closure $func) {
            $this->_function = $func;
            return $this->_function;
        }

        protected function initMethod(array $objInfo) {
            if (count($objInfo) !== 2) {
                throw new \Exception('Object or method not found.');
            }
            if (!is_object($objInfo[0])) {
                throw new \Exception('No object given.');
            }
            if (!method_exists($objInfo[0], $objInfo[1])) {
                throw new \Exception('Method does not exist in object.');
            }
            $this->_object = $objInfo[0];
            $this->_function = $objInfo[1];
            return array($this->_object, $this->_function);
        }

        protected function buildMethodReflector($object, $function) {
            $m = new \ReflectionMethod($object, $function);
            $m->setAccessible(true);
            return $m;
        }

        protected function buildFunctionReflector($function) {
            return new \ReflectionFunction($function);
        }

        protected function parseFunctionParams(array $params) {
            $info = array();
            foreach ($params as $param) {
                $info[$param->getName()] = array(
                    'name' => $param->getName(),
                    'position' => $param->getPosition(),
                    'optional' => $param->isOptional(),
                    'type' => $param->getType(),
                    'condition' => null,
                    'default_value' => ($param->isDefaultValueAvailable() ? $param->getDefaultValue() : null),
                );
            }
            $this->extractTypeInfos($this->getReflectionFunction()->getDocComment(), $info);
            return $info;
        }

        /**
         * gets a doc comment block as string and tries to extract type information
         * from it, storing in the param info array given.
         *
         * Looks for doc comments like "@param <type> <varname> ....".
         */
        protected function extractTypeInfos($docComment, &$paramInfo) {
            $matches = $this->matchParams($docComment);
            foreach ($matches['varname'] as $key => $varname) {
                if (!empty($matches['type'][$key]) && isset($paramInfo[$varname])) {
                    if (!isset($paramInfo[$varname]['type'])) {
                        $paramInfo[$varname]['type'] = $matches['type'][$key];
                    }
                    $conditionStr = (!empty($matches['condition'][$key]) ? $matches['condition'][$key] : null);
                    if ($conditionStr) {
                        $cond = Condition::getCondition($paramInfo[$varname]['type'], $conditionStr);
                        $paramInfo[$varname]['condition'] = $cond;
                    }
                }
            }
        }

        /**
         * returns an array with parameters found in a docblock string, in the form
         * "@param <type>[condition] $<name> ..."
         *
         * @return array An associative array with the following keys:
         *    - type: an array of types found (e.g. array('int','int','string'))
         *    - condition: an array of conditions, e.g. array('1..10','>0','a|b|c')
         *    - varname: an array of variable names, e.g. array('a','myVar','d')
         */
        protected function matchParams($docComment) {
            $matches = array();
            preg_match_all(
                '/@param\s+(?P<type>\w+)(\[(?P<condition>.*)\])*\s+\$(?P<varname>\w+)/',
                $docComment,
                $matches
            );
            return $matches;
        }

        /**
         * Returns the detected input parameters for the given function / method.
         *
         * @return array An associative array containing 'param_name' => array() elements with the detected parameters
         */
        public function getInputParameters() {
            return $this->_parameters;
        }

        /**
         * Invokes the configured function / method, injecting
         * the given parameters. The $args array is an array
         * with parameter name / value pairs. The order does not
         * matter, the parameters are injected in the correct order.
         *
         * The parameters are invoked depinding on the given arguments. The
         * arguments are always a key => value pair:
         * - either a parameter name => value
         * - or a Class name => value
         *
         * If the parameter can be found by name, it takes precedence.
         * Then it is checked if a parameter with the given Class name can be found.
         *
         * If the type of the expected parameter could be parsed,
         * the value is casted to that type (for non-class types).
         *
         * If a parameter, which is expected to be present is not
         * in the $params array, an exception is thrown.
         *
         * @param array $args An associative array containing the parameters and values
         *     for the function to be called, e.g.: array('a'=>1, 'b'=>'Alex')
         * @return mixed The result of the calling function / method
         */
        public function invoke(array $args = null) {
            $callParams = array();
            foreach ($this->_parameters as $expectedParam) {
                $this->assignCallParam($expectedParam, $args, $callParams);
            }
            if (!$this->allowUnknownParams && count($args) > 0) {
                throw new \Exception('Unknown Parameters found: ' . join(', ', array_keys($args)));
            }

            if ($this->_reflectionFunction instanceof \ReflectionFunction) {
                $ret = $this->_reflectionFunction->invokeArgs($callParams);
                return $this->_reflectionFunction->invokeArgs($callParams);
            } elseif ($this->_reflectionFunction instanceof \ReflectionMethod) {
                return $this->_reflectionFunction->invokeArgs($this->_object, $callParams);
            } else {
                throw new \Exception('Oops: Fatal: the callee you delivered seems not to be a function or method.');
            }
        }

        protected function assignCallParam($expectedParam, &$params, &$callParams) {
            $name = $expectedParam['name'];
            $position = $expectedParam['position'];
            $type = $expectedParam['type'];
            $value = null;
            if (array_key_exists($name, $params)) {
                $value = $params[$name];
                unset($params[$name]);
            } elseif ($type instanceof \ReflectionType) {
                // only non-builtin types can be injected by class:
                if ($type->isBuiltin() !== true) {
                    $value = $this->findParamValueWithType($params, $type);
                    unset($params[$name]);
                }
            } else {
                if ($expectedParam['optional']) {
                    $value = $expectedParam['default_value'];
                } else {
                    throw new \Exception("parameter '{$name}' is not optional.");
                }
            }

            $cond = $expectedParam['condition'];
            if ($cond instanceof Condition) {
                $this->checkParameterValidity($value, $expectedParam, $cond);
            }

            if ($expectedParam['type'] instanceof \ReflectionType && $expectedParam['type']->isBuiltin() !== true) {
                $callParams[$position] = $value;
            } elseif (!empty($expectedParam['type'])) {
                $callParams[$position] = TypeCaster::cast($value, $expectedParam['type']);
            } else {
                $callParams[$position] = $value;
            }
        }

        /**
         * finds a parameter that matches the given class type. Needed for object injection.
         */
        protected function findParamValueWithType($params, $type) {
            if ($type instanceof \ReflectionNamedType) {
                $type = $type->getName();
            } else {
                $type = (string) $type;
            }
            foreach ($params as $typeName => $value) {
                if (is_object($value) && $typeName === $type) {
                    return $value;
                }
            }
            // Not found in param array, so check for a service container:
            if ($this->hasServiceContainer()) {
                if ($this->getServiceContainer()->has($type)) {
                    return $this->getServiceContainer()->get($type);
                }
            }
            return null;
        }

        protected function checkParameterValidity($value, $expectedParam, Condition $cond) {
            $result = $cond->check($value);
            if ($result !== true) {
                if ($expectedParam['optional'] && $value == null) {
                    return true;
                }
                throw new \Exception("Parameter '{$expectedParam['name']}' of type '{$expectedParam['type']}' with value '{$value}' invalid for condition '{$cond->getConditionString()}'");
            }

            return true;
        }
    }
}
