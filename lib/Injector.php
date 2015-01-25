<?php
namespace PhpInjector {
	class Injector {
		const TYPE_FUNC = 'function';
		const TYPE_METHOD = 'method';

		protected $_object = null;
		protected $_function = null;
		protected $_type = null;
		protected $_reflectionFunction = null;
		protected $_parameters = array();

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
			} else if ($functionOrMethod instanceof \Closure) {
				$this->initClosure($functionOrMethod);
				$this->_reflectionFunction = $this->buildFunctionReflector($this->_function);
			} else if (is_array($functionOrMethod)) {
				$this->initMethod($functionOrMethod);
				$this->_reflectionFunction = $this->buildMethodReflector($this->_object, $this->_function);
			} else throw new \Exception('string or array needed in constructor.');

			$this->_parameters = $this->parseFunctionParams($this->_reflectionFunction->getParameters());
		}

		public function getReflectionFunction() {
			return $this->_reflectionFunction;
		}

		public function getFunction() {
			return $this->_function;
		}

		public function getObject() {
			return $this->_object;
		}

		protected function initOptions(array $options = null) {
			if (is_array($options)) {
				if (isset($options['allow_unknown_params'])) {
					$this->allowUnknownParams = BooleanTypeCaster::cast($options['allow_unknown_params']);
				}
			}
		}

		protected function initFunction($funcName) {
			if (function_exists($funcName)) {
				$this->_function = $funcName;
			} else {
				throw new \Exception('Function not found: '.$funcName);
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
			return array($this->_object,$this->_function);
		}

		protected function buildMethodReflector($object, $function) {
			return new \ReflectionMethod($object, $function);
		}

		protected function buildFunctionReflector($function) {
			return new \ReflectionFunction($function);
		}

		protected function parseFunctionParams(array $params) {
			$info = array();
			foreach($params as $param) {
				$info[$param->getName()] = array(
					'name' => $param->getName(),
					'position' => $param->getPosition(),
					'optional' => $param->isOptional(),
					'type' => null,
					'condition' => null,
					'default_value' => ($param->isDefaultValueAvailable()?$param->getDefaultValue():null)
				);
			}
			$this->extractTypeInfos($this->getReflectionFunction()->getDocComment(),$info);
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
			foreach($matches['varname'] as $key=>$varname) {
				if (!empty($matches['type'][$key]) && isset($paramInfo[$varname])) {
					$paramInfo[$varname]['type'] = $matches['type'][$key];
					$conditionStr = (!empty($matches['condition'][$key])?$matches['condition'][$key]:null);
					if ($conditionStr) {
						$cond = Condition::getCondition($paramInfo[$varname]['type'], $conditionStr);
						$paramInfo[$varname]['condition'] = $cond;
					}
				}
			}
		}

		protected function matchParams($docComment) {
			$matches = array();
			preg_match_all(
				'/@param\s+(?P<type>\w+)(\[(?P<condition>.*)\])*\s+\$(?P<varname>\w+)/',
				$docComment,
				$matches);
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
		 * If the type of the expected parameter could be parsed,
		 * the value is casted to that type.
		 *
		 * If a parameter, which is expected to be present is not
		 * in the $params array, an exception is thrown.
		 *
		 * @return mixed The result of the calling function / method
		 */
		public function invoke(array $args = null) {
			$callParams = array();
			foreach($this->_parameters as $expectedParam) {
				$this->assignCallParam($expectedParam, $args, $callParams);
			}
			if (!$this->allowUnknownParams && count($args) > 0) {
				throw new \Exception('Unknown Parameters found: '.join(', ',array_keys($args)));
			}
			
			if ($this->_reflectionFunction instanceof \ReflectionFunction) {
				$ret = $this->_reflectionFunction->invokeArgs($callParams);
				return $this->_reflectionFunction->invokeArgs($callParams);
			} else if ($this->_reflectionFunction instanceof \ReflectionMethod) {
				return $this->_reflectionFunction->invokeArgs($this->_object, $callParams);
			} else {
				throw new \Exception('Oops: Fatal: the callee you delivered seems not to be a function or method.');
			}
		}

		protected function assignCallParam($expectedParam, &$params, &$callParams) {
			$name = $expectedParam['name'];
			$position = $expectedParam['position'];
			$value = null;
			if (isset($params[$name])) {
				$value = $params[$name];
				unset($params[$name]);
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

			if (!empty($expectedParam['type'])) {
				$callParams[$position] = TypeCaster::cast($value, $expectedParam['type']);
			} else {
				$callParams[$position] = $value;
			}
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