![Tests](https://github.com/bylexus/php-injector/actions/workflows/run-tests.yml/badge.svg)

# php-injector

> A Function / Method parameter injection and Class autowire helper. Makes your Dependency Injection work.

- [Features](#features)
- [Installation](#installation)
- [Summary - Method / Function invocation](#summary-method-function-invocation)
  - [Scenario: Dependency Injection](#scenario-dependency-injection)
  - [Scenario: Web Request parameter injection](#scenario-web-request-parameter-injection)
- [Function / Method definition](#function-method-definition)
  - [No type casting / conditions](#no-type-casting-conditions)
  - [Injecting by Class types (Object injection)](#injecting-by-class-types-object-injection)
  - [Force type casting / parameter conditions](#force-type-casting-parameter-conditions)
    - [DocBlock Comment syntax](#docblock-comment-syntax)
    - [Supported types](#supported-types)
    - [Using parameter conditions](#using-parameter-conditions)
    - [Available conditions](#available-conditions)
- [Summary - AutoWiring of Classes](#summary-autowiring-of-classes)
  - [Using manual parameters for class construction](#using-manual-parameters-for-class-construction)
- [Examples - Function / Method injection](#examples-function-method-injection)
  - [Simple function parameter injection](#simple-function-parameter-injection)
  - [Parameter injection with type casting](#parameter-injection-with-type-casting)
  - [Parameter injection with type casting and conditions](#parameter-injection-with-type-casting-and-conditions)
  - [using native PHP functions](#using-native-php-functions)
  - [Inject class objects](#inject-class-objects)
  - [Inject services from a PSR-11 Service Container](#inject-services-from-a-psr-11-service-container)
- [Examples - Auto-Wiring classes](#examples-auto-wiring-classes)
  - [Simple Auto-Wiring](#simple-auto-wiring)
  - [Auto-Wiring with manual parameters](#auto-wiring-with-manual-parameters)
  - [Auto-Wiring with a PSR-11 Service Container](#auto-wiring-with-a-psr-11-service-container)
- [Developing](#developing)
  - [setup dev env](#setup-dev-env)
  - [run unit tests](#run-unit-tests)
  - [create and deploy a new version](#create-and-deploy-a-new-version)
- [Compatibility](#compatibility)
- [Changelog](#changelog)
  - [V4.0.0](#v400)
  - [V3.0.0](#v300)
- [License](#license)


## Features

* Dependency Injection: Meant to use as a helper library in a dependency injection mechanism
  to call your functions / methods with the needed parameters
* allows invocation of functions / methods with a parameter array matching the function's parameters
* allows 'auto-wiring' of classes by injecting dependant constructor parameters upon instantiation
* extracts the parameters (and their names) from the function / method / constructor signature
* extracts the parameter's default values form the function / method / constructor signature
* Allows the user to define types and even conditions in a simple DocBlock format, if needed
* helps you to make parameter validation / conversion. Especially useful when
  used in frontend-faced Controllers.
* Check input variable for matching conditions (e.g. string length, number in a certain range, date > now etc...)
* Can be used to inject parameters by name as well as by Class type
* Supports resolving parameters from a PSR-11 Service Container

## Installation

via [Composer](https://getcomposer.org/):

<code>composer require bylexus/php-injector</code>

Then just use the composer's autoload facility:
```php
require_once('vendor/autoload.php');

// Method invocation:
$injector = new \PhpInjector\Injector('myfunction');

// Class autowiring:
$aw = new \PhpInjector\AutoWire($diContainer);
$serviceA = $aw->createInstance(ServiceA::class);
```

## Summary - Method / Function invocation

The PHP Injector helps you calling functions / methods with parameter injection.
There are two use cases for this application:

* to call functions / methods in a Dependency Injection scenario
* to invoke a function with request parameters, but with a type check mechanism in place

### Scenario: Dependency Injection

In Dependency Injection scenarios, a developing user want to request a service
from a dependency injection container. The easiest way is to allow the developer to
just state the service type as function argument:

```php
public function myFunction(Request $request) {
    $param = $request->input('param);
}
```

The Request object in this case gets magically injected by the surrounding framework.
This is where PHPInjector comes into play: It allows the framework builder to
inject the requested services.

### Scenario: Web Request parameter injection

Under some circumstances it is needed or wanted to call a function
not directly but to "collect" the parameters somehow dynamically and
then inject them to a certain function.

Why?

Think for example you want to pass Web request parameters as function
parameters of a controller, like this:

```php
$_REQUEST = array('name' => 'Alex','age' => '24', 'active' => 'true');

function storePersonInfo($name, $age, $active = false) {
	// ... do some complex things
}

$res = storePersonInfo($_REQUEST);
```

**Now THAT is certainly a bad idea**! Don't use request parameters as function parameters directly!
All kind of bad things can happen here (injections, remote code invocation ...).

This is where the Injector comes into play: It allows you to:

1. define which parameters you want to accept for a function
2. call the function with an array of parameters, indepentent of their order
   (so you don't have to implement a complex variable ordering algorithm)
3. define a TYPE converstion, e.g. "I accept an $age variable, but it is casted to an int."
4. even allows you to define conditions (e.g. 'age must be > 20')

Using the Injector, this looks as follows:

```php
$_REQUEST = array('name' => 'Alex','age' => '24','active' => 'true');

/**
 * @param string $name A name
 * @param int[>20] $age The age
 * @param bool $active
 */
function storePersonInfo($name, $age, $active = false) {
    // here, $age ist casted to an int, and must be > 20,
    // while $active is a proper boolean (true) instead of a string.
}

// Invoke the function via injector:
$injectStore = new Injector('storePersonInfo');
$res = $injectStore->invoke($_REQUEST);
```

The injector makes sure that your request parameters are passed to the function
in the correct order AND cast the types, so in the example, <code>$age</code> is a properly casted Integer.

This also works in object methods:

```php
$_REQUEST = array('name' => 'Alex','age' => '24','active' => 'true');

class MyController {
    /**
     * @param string $name A name
     * @param int[>20] $age The age
     * @param bool $active
     */
    public function storePersonInfo($name, $age, $active = false) {
        // here, $age ist casted to an int, and must be > 20,
        // while $active is a proper boolean (true) instead of a string.
    }

}

// Invoke the method via injector:
$controller = new Controller();
$injectStore = new Injector(array($controller,'storePersonInfo'));
$res = $injectStore->invoke($_REQUEST);
```

## Function / Method definition

### No type casting / conditions

If you just want to call your functions / methods without do any type casting / conditions, you just define your functions as usual:

```php
// Normal function:
function teaser($text, $maxlen = 80, $tail = '...') {
    if (mb_strlen($text) > $maxlen) {
        return mb_substr($text,0,$maxlen-mb_strlen($tail)) . $tail;
    } else {
        return $text;
    }
}

// Class method:
class TextHandler {
    public function teaser($text, $maxlen = 80, $tail = '...') {
        if (mb_strlen($text) > $maxlen) {
            return mb_substr($text,0,$maxlen-mb_strlen($tail)) . $tail;
        } else {
            return $text;
        }
    }
}
```

You can then use the Injector to invoke the functions (note that the argument array parameters don't have to be in the function args order):
```php
// Function injector:
$injector = new \PhpInjector\Injector('teaser');
$ret = $injector->invoke(array(
    'maxlen' => 10,
    'text' => 'My fancy long text that gets cut'
);

// Method injector:
$th = new TextHandler();
$injector = new \PhpInjector\Injector(array($th,'teaser'));
$ret = $injector->invoke(array(
    'maxlen' => 10,
    'text' => 'My fancy long text that gets cut'
);
```

In this example, the <code>teaser</code> function / methods will be called with the <code>$text</code>, the <code>$maxlen = 10</code> and the <code>$tail = '...'</code> arguments.

### Injecting by Class types (Object injection)

If you want to match parameters by Class type (not by parameter name), you just type-hint the class:

```php
function doSome(\Psr\Http\Message $message) {
    // do something with the $message object
}
```

This method can be invoked as follows:

```php
$injector = new \PhpInjector\Injector('doSome');
$ret = $injector->invoke(array(
    'Psr\Http\Message' => new HttpMessage()
);
```

Note that you have to provide the full namespaced class name for a match in the parameter array.

### Force type casting / parameter conditions

The big advantage of the PhpInjector is its ability to cast input parameters to a certain (base) type, and to check the values if they match certain conditions. This is especially useful for frontend input parameter validation.

Because PHP's lack of proper type hinting (base types cannot be used as hints in function signature), we decided to use the standard DocBlock Comments for describing parameters. This has the advantage that we also can define special types and conditions in a standard and readable syntax, without interfering
with the default PHP function definition.

#### DocBlock Comment syntax

The syntax is similar to the [PhpDocumentor's <code>@param</code> definition](http://www.phpdoc.org/docs/latest/references/phpdoc/tags/param.html):

> <code>@param [Type] [name] [description]</code>

An example:

```php
/**
 * A fancy function with some weird input params
 *
 * @param string $param_a The name of some monster
 * @param int $param_b The age of the monster
 * @param string $param_c The favourite color of the monster
 * @param bool $param_d Is the monster dangerous?
function funcWithSomeParams($param_a, $param_b, $param_c = 'red', $param_d = false) {}
```

This is perfectly compatible with the standard DocBlock comment for e.g. PhpDocumentor, but at the same time gives the Injector the needed information to cast the input values.

#### Supported types

Standard PHP types:

* <code>bool</code>, <code>boolean</code>
* <code>int</code>, <code>integer</code>
* <code>float</code>, <code>double</code> (results in <code>float</code>)
* <code>string</code>
* <code>array</code>
* <code>object</code>
* <code>mixed</code> (no casting is done)

Special types:

* <code>timestamp</code>: converts the parameter to a Unix timestamp, trying to guess the input via <code>strtotime</code> (e.g.: Input: '22.01.2015 22:00', parameter value: 1421964000)
* <code>json</code>: Converts the input via <code>json_decode</code>

#### Using parameter conditions

> (not yet implemented)

Especially for frontend input validation it is useful to check the input value if they match certain conditions, e.g. if a number is in a given range, if a string fits into a maxlength, if a date is in a certain range etc. PhpInjector comes with a set of condition definitions. We also use the DocBlock comment as shown above, and extend the <code>Type</code> field as shown in the following example:

```php
/**
 * A fancy function with some weird input params
 *
 * @param string[<100] $param_a The name of some monster, max. 100 chars
 * @param int[>=0] $param_b The age of the monster
 * @param timestamp[>=01.01.2000] $param_c Date of viewing, min 1.1.2000
 * @param bool $param_d Is the monster dangerous?
function funcWithSomeParams($param_a, $param_b, $param_c = 'red', $param_d = false) {}
```

This defines some conditions for the input parameters. If they do not match, an exception is thrown when invoking the metod via Injector.

#### Available conditions

* <code><, <=, >, >=, <> [nr|timestamp]</code>
    - numbers: Input must match the comparison (e.g. <=100: input must be less or equal 100)
    - strings: input length must match the comparison
    - timestamps: input date/time must match the comparison date/time
* <code>[lower]..[upper]</code>: Input must be within the border
    - For numbers: e.g. <code>int[1..100]</code>: Input value must be between 1 to 100 (including 1 and 100)
    - For strings: e.g. <code>string[5..20]</code>: Input must be at least 5 characters, but max. 20 characters long
    - For timestamps: e.g. <code>timestamp[1.1.2000..31.12.2010]</code>: Input date must be within the given date range
* <code>word1[[|word2]...]</code>: Input string must contain one of the words.
  Example: <code>@param string[word1|word2|word3] $str</code>

## Summary - AutoWiring of Classes

AutoWiring is useful if you have service classes that depend on other services. For example:

```php
// ServiceB is independant
class ServiceB {}

// ServiceA depends on ServiceB:
class ServiceA {
    public ServiceB $b;
    public function __construct(ServiceB $b) {
        $this->b = $b;
    }
}
```

In this scenario, `ServiceA` can only be instantiated by retrieving `ServiceB` as its constructor parameter.
This is a typical use case in Dependency Injection scenarios.

The `AutoWire` class helps you resolve the dependencies and instantiate a dependant class:

```php
use PhpInjector\AutoWire;
 
 $aw = new AutoWire();
 // In this case, ServiceB will also be instantiated and injected:
 $instA = $aw->createInstance(ClassNameA::class);
```

If you're using a Dependency Injection container, you can use auto-wiring while setting up service instances:

```php
use PhpInjector\AutoWire;

// a PSR-11-compatible service container:
$container = new Container();


// register the container with the AutoWire class:
$aw = new AutoWire($container);

// register ServiceB, which is independant. We could just use `new ServiceB()` here,
// but we can also ask the AutoWire class to create an instance:
// This makes the instantiation of ServiceB future-proof, e.g.
// if ServiceB becomes a dependant of a ServiceC in the future:
$container->set(ServiceB::class, $aw->createInstance(ServiceB::class));

// register ServiceA, while ServiceB is fetched from the containe:
$container->set(ServiceA::class, $aw->createInstance(ServiceA::class));
```

### Using manual parameters for class construction

Sometimes a class needs more than just other services in the constructor. Consider the following class:

```php
class ServiceC {
    private $b;
    private $name;
    private $isActive;
    public function __construct(ServiceB $b, string $name, bool $isActive) {
        $this->b = $b;
        $this->name = $name;
        $this->isActive = $isActive;
    }
}
```

Here, `$name` and `$isActive` cannot be determined as a `type` or service to be injected.
In this cases, you can use additional parameters when auto-wiring a class:

```php
use PhpInjector\AutoWire;

$aw = new AutoWire($container);
$instC = $aw->createInstance(ServiceC::class, [
    'isActive' => true,
    'name' => 'Alex',
]);
```

The additional parameters are passed to the constructor of the class, if the name matches.

## Examples - Function / Method injection

### Simple function parameter injection

```php
require_once('vendor/autoload.php');

// Function to use for injection:
function fact($n) {
    if ($n > 1) {
        return $n*fact($n-1);
    } else return 1;
}

// Injector with no type casting / conditions:
$injector = new \PhpInjector\Injector('fact');
$ret = $injector->invoke(array('n'=>4)); // 24
```

### Parameter injection with type casting

```php
require_once('vendor/autoload.php');

// Function to use for injection: DocBlock defines the casting type:
/**
 * calculates the factorial of n.
 *
 * @param int $n The value to calculate the factorial from
 */
function fact($n) {
    // here, $n is casted to an int:
    if ($n > 1) {
        return $n*fact($n-1);
    } else return 1;
}

// Injector with type casting to int:
$injector = new \PhpInjector\Injector('fact');
$ret = $injector->invoke(array('n'=>4.5)); // 24
```

### Parameter injection with type casting and conditions

```php
require_once('vendor/autoload.php');

// Function to use for injection: DocBlock defines the casting type and condition:
/**
 * calculates the factorial of n.
 *
 * @param int[>0] $n The value to calculate the factorial from
 */
function fact($n) {
    // here, $n is casted to an int. An exception is thrown when $n is < 1.
    if ($n > 1) {
        return $n*fact($n-1);
    } else return 1;
}

// Injector with type casting to int:
$injector = new \PhpInjector\Injector('fact');
$ret = $injector->invoke(array('n'=>4.5)); // 24
```

### using native PHP functions

You can even use native PHP functions:
```php
$inj = new \PhpInjector\Injector('strtolower');
echo $inj->invoke(array('str'=>'HEY, WHY AM I SMALL?'));
```

### Inject class objects

You can also inject class object by type name, instead of variable name. This is exceptionnaly useful if you want to
implement a Dependency Injection framework within your calls:

```php
// Function that receives an object of type 'Psr\Http\Request':
function processRequest(\Psr\Http\Request $req, $param1, $param2) {
    // process Request
}
```

To call this function, you can use the Invoker as follows:

```php
$inj = new \PhpInjector\Injector('processRequest');
echo $inj->invoke(['Psr\Http\Request' => new Request(), 'param1' => 'foo', 'param2' => 'bar']);
```

### Inject services from a PSR-11 Service Container

You can provide a [PSR-11 Service Container](https://www.php-fig.org/psr/psr-11/) if the method's type signature requests a Service class from such a service container. This is especially useful in Dependency Injection scenarios:

```php
use MyServices\FooService;

// Function that receives an object of type 'Psr\Http\Request':
function processRequest(FooService $myService, $param1) {
    $myService->doSome($param1);
}
```
To call this function, you can use the Invoker as follows:

```php
// Service container with available service, created / intantiated elswhere:
$container = get_the_PSR_11_Service_container();
$container->registerService(new FooService());

// Now just give the service container in the options array:
$inj = new \PhpInjector\Injector('processRequest', ['service_container' => $container]);
echo $inj->invoke(['param1' => 'foo']);


// Or, optionally, set the service container later:
$inj = new \PhpInjector\Injector('processRequest');
$inj->setServiceContainer($container);
echo $inj->invoke(['param1' => 'foo']);
```

## Examples - Auto-Wiring classes

### Simple Auto-Wiring

In its simplest form, you don't need a DI container, it's enough if the dependant classes
exists. In this case, AutoWire just instantiates those classes:

```php
use PhpInjector\AutoWire;

class ServiceA {}
class ServiceB {
    public function __construct(ServiceA $a) {}
}

$aw = new AutoWire();
// here, ServiceA is just instantiated with "new ServiceA()", along with ServiceB:
$b = $aw->createInstance(ServiceB::class);
```

### Auto-Wiring with manual parameters

If you don't have a service container, or want to deliver additional constructor arguments manually,
you can just provide a parameter array:

```php
use PhpInjector\AutoWire;

class ServiceA {}
class ServiceB {
    public function __construct(ServiceA $a, string $name) {}
}

$aw = new AutoWire();
// here, ServiceA is just instantiated with "new ServiceA()", along with ServiceB:
$b = $aw->createInstance(ServiceB::class, [
    'name' => 'foo',
    new ServiceA(),
]);
```

Here, `$name` is found by name in the parameter array, while `$a` is found by its Type declaration:
the parameter array is searched for a matching type, and assigned.


### Auto-Wiring with a PSR-11 Service Container

If you're using a PSR-11 compatible dependency injection container, you can use it for
AutoWire to lookup its dependencies:

```php
use PhpInjector\AutoWire;

// ServiceB is independant
class ServiceB {}

// ServiceA depends on ServiceB:
class ServiceA {
    public ServiceB $b;
    public function __construct(ServiceB $b) {
        $this->b = $b;
    }
}

// a PSR-11-compatible service container:
$container = new Container();

// register the container with the AutoWire class:
$aw = new AutoWire($container);

// ServiceB is indepenadnt
$container->set(ServiceB::class, new ServiceB());

// register ServiceA, while ServiceB is fetched from the containe:
$container->set(ServiceA::class, $aw->createInstance(ServiceA::class));
```

## Developing

### setup dev env

```shell
# Without docker:
$ git clone git@github.com:bylexus/php-injector.git
$ cd php-injector
$ composer install
$ composer test
```

```shell
# With docker:
$ git clone git@github.com:bylexus/php-injector.git
$ cd php-injector
$ docker build -t php-injector docker/
$ docker run --name php-injector -ti -v "$(pwd):/src" php-injector bash
docker> composer install
docker> composer test
```

### run unit tests

```shell
$ composer test
```

or manually, using PHPUnit:

```shell
$ php vendor/bin/phpunit ./tests
```

### create and deploy a new version

1. Update the Changelog (README.md)
2. Bump the version in composer.json
3. Merge the branches to the master branch
4. Tag the version with a git tag
5. push the tag to github

## Compatibility

* V1.0.0: PHP >= 7.0 is needed
* V1.2.0: PHP >= 7.2 is needed
* V2.0.0: PHP >= 7.4 is needed
* V3.0.0: PHP >= 8.0 is needed
* V4.0.0: PHP >= 8.1 is needed

## Changelog

### V4.0.0

- [breaking] PHP >= 8.1 is now required

### V3.0.0

- [breaking] non-accessible class methods can no longer be called (previously allowed even for private methods)
- [feature] Introducing AutoWire: create class instances by injecting constructor parameters automagically

## License

Licensed under the MIT license, copyright 2015-2024 Alexander Schenkel
