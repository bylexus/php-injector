php-injector
============

> a variable injector helper for functions / methods

Summary
------------

UNDER CONSTRUCTION!

Under some circumstances it is needed or wanted to call a function
not directly but to "collect" the variables somehow dynamically and
then inject them to a certain function.

Why?

Think for example you want to pass Web request variables as function
parameters of a controller, like this:

```
<?php
$_REQUEST = array('name' => 'Alex','age' => 24);

function storePersonInfo($name, $age) {
	// ... do some complex things
}

storePersonInfo($_REQUEST);
```

Now everyone cries "Booooh! Don't use request variables as function parameters!". And right your are.

This is where the Injector comes into play: It allows you to:

1. define which variables you want to accept for a function
2. call the function with an array of parameters, indepentent of their order
   (so you don't have to implement a complex variable ordering algorithm)
3. define a TYPE converstion, e.g. "I accept an $age variable, but it is casted to an int."

Using the Injector, this then looks as follows:

```
<?php
$_REQUEST = array('name' => 'Alex','age' => 24);

/**
 * @param string $name A name
 * @param int $age The age
 */
function storePersonInfo($name, $age) {
    // ... do some complex things with string $name and int $age
}
$injectStore = new Injector('storePersonInfo');
$injectStore->invoke($_REQUEST);
```

The injector makes sure that your request variables are passed to the function
in the correct order AND cast the types, so in the example, <code>$age</code> is a properly casted Integer.

Work In Progress
-----------------
a lot to do, not yet done, initial commit only. Here's what to expect later on:

* available as composer package
* create function and class method injections
* cast to base types (int, float, string, bool, array,object)
* cast to special types (time, json, ...)
* create curry function
* fail on wrong/unsupported parameters
* ...

