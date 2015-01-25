<?php
namespace PhpInjector {
	abstract class Condition {
		protected $_compareFunctions;
		protected $_matches;
		protected $_compareFunction;
		protected $_conditionString;



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
			$this->_compareFunctions = array_merge($this->_compareFunctions,$additionalCompareFunctions);
		}

		/**
		 * Searches for the condition string that matches one of the
		 * comparator patterns and sets the comparator functions
		 * as well as the matches from the comparator pattern
		 */
		protected function initCompareFunction($conditionString) {
			$this->_matches = array();
			$this->_compareFunction = null;
			foreach($this->_compareFunctions as $pattern => $func) {
				$matches = array();
				if (preg_match($pattern,$conditionString,$matches)) {
					$this->_matches = $matches;
					$this->_compareFunction = $func;
					break;
				}
			}

			if (!$this->_compareFunction) {
				throw new \Exception('Could not match a comparator for '.$conditionString);
			}
		}

		public function check($value) {
			$f = $this->_compareFunction;
			return $f($value, $this->_matches);
		}
	}
}
