<?php

/**
 * AutoWire - Instantiate classes by auto-wiring constructor parameters
 *
 * "Auto-Wiring" is the process of instantiating a class by automatically injecting
 * the values of constructor parameters into the class. The AutoWire class automatically
 * looks for constructor parameters and injects them.
 *
 * Usage example:
 *
 * <code>
 * // ServiceB is independant
 * class ServiceB {}
 *
 * // ServiceA depends on ServiceB:
 * class ServiceA {
 *     public ServiceB $b;
 *     public function __construct(ServiceB $b) {
 *         $this->b = $b;
 *     }
 * }
 *
 * $aw = new AutoWire();
 * // In this case, ServiceB will also be instantiated and injected:
 * $instA = $aw->createInstance(ClassNameA::class);
 * </code>
 *
 * The standard case is to pass a service dependency injection container to the constructor of AutoWire,
 * which has its global services registered.
 *
 * It looks in the following places for valid parameters:
 * - in the given params array, matching the parameter name with a key in the params array
 * - in the given params array, matching the parameter type with a value's type in the params array
 * - in the service container, if set
 * - If there is a class that matches a type, it is (auto-wired) instantiated and used
 * - using the default value of the parameter
 * - using 'null' if the parameter is nullable
 *
 * Note that there are limitations for auto-wiring classes with constructor dependencies:
 *
 * - Explicit type hinting only: A type hint must be explicit and unique. That means the
 *   auto-wire process cannot inject optional types (?TypeName) or union types (TypeNameA|TypeNameB),
 *   as it must know the exact type to search.
 *
 * @author Alexander Schenkel, alex@alexi.ch
 * @copyright 2015-2024 Alexander Schenkel
 * @link https://github.com/bylexus/php-injector
 *
 * released under the MIT license, @link http://opensource.org/licenses/MIT
 */

namespace PhpInjector;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionParameter;
use ReflectionType;

class AutoWire {
    protected ?ContainerInterface $container = null;

    /** @var array<class-string, ReflectionClass> */
    protected array $reflClassCache = [];

    /** @var array<class-string, array<ReflectionParameter>> */
    protected array $constructorParamsCache = [];

    public function __construct(?ContainerInterface $container = null) {
        $this->container = $container;
    }

    /**
     * Returns the (cached) instance of the ReflectionClass for the given class name.
     *
     * @param class-string $className
     * @return ReflectionClass
     */
    protected function getReflectionClass(string $className) {
        if (!isset($this->reflClassCache[$className])) {
            $this->reflClassCache[$className] = new ReflectionClass($className);
        }
        return $this->reflClassCache[$className];
    }

    /**
     * Returns the (cached) array of ReflectionParameters for the given class's constructor.
     *
     * @param class-string $className
     * @return \ReflectionParameter[]
     */
    protected function getConstructorParams(string $className) {
        if (!isset($this->constructorParamsCache[$className])) {
            $classReflection = $this->getReflectionClass($className);
            $constr = $classReflection->getConstructor();
            $this->constructorParamsCache[$className] = $constr ? $constr->getParameters() : [];
        }
        return $this->constructorParamsCache[$className];
    }

    /**
     * @template T
     * @param class-string<T> $className
     * @param null|array<mixed> $params
     * @return T
     */
    public function createInstance(string $className, array|null $params = null) {
        $refClass = $this->getReflectionClass($className);
        $wantedConstructorParams = $this->getConstructorParams($className);
        $callingParams = [];
        foreach ($wantedConstructorParams as $wantedParam) {
            $paramName = $wantedParam->getName();
            // check if we get an explicitely named parameter:
            // Note that if we have an explicit parameter, we do no type check:
            if (isset($params[$paramName])) {
                $callingParams[$paramName] = $params[$paramName];
                // remove the used parameter from the params array, we do not want to re-use it again:
                unset($params[$paramName]);
                continue;
            }

            // check if the given parameters has a value that matches the type:
            $paramValue = $this->findMatchingValueForType($wantedParam->getType(), $params ?? []);
            if ($paramValue) {
                // remove the used parameter from the params array, we do not want to re-use it again:
                if (is_array($params)) {
                    unset($params[array_search($paramValue, $params)]);
                }
                $callingParams[$paramName] = $paramValue;
                continue;
            }

            // check if the DI container hat a value:
            $diParamValue = $this->findService($wantedParam->getType());
            if ($diParamValue) {
                $callingParams[$paramName] = $diParamValue;
                continue;
            }

            // check if there is a class with that name that can be invoked:
            if (class_exists($wantedParam->getType())) {
                // recursively instantiate the class: Note that this _may_ be a very
                // bad idea and needs more checking (e.g. loops...)
                $callingParams[$paramName] = $this->createInstance($wantedParam->getType(), $params);
                continue;
            }

            // check if we can fill the param with its default value:
            if ($wantedParam->isDefaultValueAvailable()) {
                $callingParams[$paramName] = $wantedParam->getDefaultValue();
                continue;
            }
            // is it nullable?
            if ($wantedParam->allowsNull()) {
                $callingParams[$paramName] = null;
                continue;
            }
            // if all else fails to determine the param's value, we have to give up:
            throw new \InvalidArgumentException(sprintf('Cannot find a value for parameter "%s" of type "%s" in class "%s".', $paramName, $wantedParam->getType(), $className));
        }

        return $refClass->newInstanceArgs($callingParams);
    }

    protected function findService(?ReflectionType $type) {
        if (!$this->container) {
            return null;
        }
        if (!$type) {
            return null;
        }
        return $this->container->get((string)$type);
    }
    protected function findMatchingValueForType(?ReflectionType $type, array $values) {
        foreach ($values as $value) {
            if ($value instanceof ((string)$type)) {
                return $value;
            }
        }
        return null;
    }
}
