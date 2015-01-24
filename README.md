php-injector
============

> a parameter injector for functions / object methods

Features
---------
* allows you to invoke functions / methods with a parameter array
* extracts the parameters (and their names) from the function / method signature
* extracts the parameter's default values form the function / method signature
* Define types and even conditions in a simple DocBlock format
* helps you to make parameter validation / conversion. Especially useful when
  used in frontend-faced Controllers.

Summary
------------

UNDER CONSTRUCTION!

Under some circumstances it is needed or wanted to call a function
not directly but to "collect" the parameters somehow dynamically and
then inject them to a certain function.

Why?

Think for example you want to pass Web request parameters as function
parameters of a controller, like this:

```
<?php
$_REQUEST = array('name' => 'Alex','age' => '24', 'active' => 'true');

function storePersonInfo($name, $age, $active = false) {
	// ... do some complex things
}

$res = storePersonInfo($_REQUEST);
```

Now everyone cries "Booooh! Don't use request parameters as function parameters!". And right your are.

This is where the Injector comes into play: It allows you to:

1. define which parameters you want to accept for a function
2. call the function with an array of parameters, indepentent of their order
   (so you don't have to implement a complex variable ordering algorithm)
3. define a TYPE converstion, e.g. "I accept an $age variable, but it is casted to an int."
4. even allows you to define conditions (e.g. 'age must be > 20')

Using the Injector, this looks as follows:

```
<?php
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

```
<?php
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


Function / Method definition
-----------------------------

### No type casting / conditions

If you just want to call your functions / methods without do any type casting / conditions, you just define your functions as usual:

```
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
```
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

### Force type casting / parameter conditions

The big advantage of the PhpInjector is its ability to cast input parameters to a certain (base) type, and to check the values if they match certain conditions. This is especially useful for frontend input parameter validation.

Because PHP's lack of proper type hinting (base types cannot be used as hints in function signature), we decided to use the standard DocBlock Comments for describing parameters. This has the advantage that we also can define special types and conditions in a standard and readable syntax, without interfering
with the default PHP function definition.

#### DocBlock Comment syntax

The syntax is similar to the [PhpDocumentor's <code>@param</code> definition](http://www.phpdoc.org/docs/latest/references/phpdoc/tags/param.html):

> <code>@param [Type] [name] [<description>]</code>

An example:

```
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

Especially for frontend input validation it is useful to check the input value if they match certain conditions, e.g. if a number is in a given range, if a string fits into a maxlength, if a date is in a certain range etc. PhpInjector comes with a set of condition definitions. We also use the DocBlock comment as shown above, and extend the <code>Type</code> field as shown in the following example:

```
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

* <code><, <=, =, >, >= [number]</code>
    - numbers: Input must match the comparison (e.g. <=100: input must be less or equal 100)
    - strings: input length must match the comparison
* <code>[lower]..[upper]</code>: Input must be within the border
    - For numbers: e.g. <code>int[1..100]</code>: Input value must be between 1 to 100 (including 1 and 100)
    - For strings: e.g. <code>string[5..20]</code>: Input must be at least 5 characters, but max. 20 characters long
    - For timestamps: e.g. <code>timestamp[1.1.2000..31.12.2010]</code>: Input date must be within the given date range

Examples
-----------



Work In Progress
-----------------
a lot to do, not yet done, initial commit only. Here's what to expect later on:

* available as composer package
* cast to special types (time, json, ...)
* support for min/max/range definitions (or validation)
  (e.g. int[>0], int[1..100]), timestamp[01.01.2000..31.12.2000], string[<100]
* define own conditions
* fail on wrong/unsupported parameters
* throw specific errors
* ...

