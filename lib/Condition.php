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
	abstract class Condition {
		protected $_compareFunctions;
		protected $_matches;
		protected $_compareFunction;
		protected $_conditionString;


		/**
		 * Returns the specific instance of a Condition class, depending on the given
		 * data type.
		 *
		 * @param string $type The data type, e.g. 'int'
		 * @param string $conditionString, e.g. '1..100'
		 * @param array $additionalCompareFunctions add your own compare function.
		 *    @see StringCondition for an example / layout of the array.
		 * @return Condition The specific Condition instance
		 */
		public static function getCondition($type, $conditionString, array $additionalCompareFunctions = null) {
			switch (strtolower($type)) {
				case 'int':
				case 'integer':
				case 'float':
				case 'double':
					return new NumberCondition($conditionString, $additionalCompareFunctions);
				case 'string':
					return new StringCondition($conditionString, $additionalCompareFunctions);
				case 'timestamp':
					return new TimestampCondition($conditionString, $additionalCompareFunctions);
			}

			return null;
		}

		public function __construct($conditionString, array $additionalCompareFunctions = null) {
			$this->_conditionString = trim($conditionString);
			$this->_compareFunctions = $this->getInternalCompareFunctions();
			if (is_array($additionalCompareFunctions)) {
				$this->addAdditionalCompareFunctions($additionalCompareFunctions);
			}
			$this->initCompareFunction($this->_conditionString);
		}

		abstract protected function getInternalCompareFunctions();

		public function getConditionString() {
			return $this->_conditionString;
		}

		protected function addAdditionalCompareFunctions(array $compareFunctions) {
			$this->_compareFunctions = array_merge($this->_compareFunctions, $compareFunctions);
		}

		/**
		 * Searches for the condition string that matches one of the
		 * comparator patterns and sets the comparator functions
		 * as well as the matches from the comparator pattern
		 */
		protected function initCompareFunction($conditionString) {
			$this->_matches = array();
			$this->_compareFunction = null;
			foreach ($this->_compareFunctions as $pattern => $func) {
				$matches = array();
				if (preg_match_all($pattern, $conditionString, $matches)) {
					$this->_matches = $matches;
					$this->_compareFunction = $func;
					break;
				}
			}

			if (!$this->_compareFunction) {
				throw new \Exception('Could not match a comparator for ' . $conditionString);
			}
		}

		public function check($value) {
			$f = $this->_compareFunction;
			return $f($value, $this->_matches);
		}
	}
}
