# PHP-Deferrable - Simple and Powerful deferrable run code library

The `PHP-Deferrable` is a simple and powerful deferrable run code library.
This library like Golang.
This library is very simple because this is not depending other libraries.

## Documents
- English (Current)
- [日本語](./readme-ja.md) 

## Install

Use composer:
```
composer require m3m0r7/php-deferrable
```

## Issues to date
Go has a defer, and you can execute the contents of the defer before returning.
However, although PHP does not have a defer, it is possible to achieve defer using `try-finally` or destructor destruction timing.

```php
try {
    // ... do something
} finally {
    // post-processing
}
```

This has some problems: the post-processing code can be cumbersome, and if the `try` syntax gets too long, you won't know what to do.
And you will suffer from unnecessary indentation.

`php-deferrable` solves all of these problems by providing very simple functions and classes to solve the problem.

## Quick Start
```php
use function PHPDeferrable\defer;
use function PHPDeferrable\deferrable;

class MyClass
{
    public function doSomething1()
    {
        defer(function () {
            echo "Three!\n";
        });

        defer(function () {
            echo "Two!\n";
        });
        echo "One!\n";
    }

    public function doSomething2()
    {
        defer(function () {
            echo "NyanNyan!\n";
        });
        echo "Wanwan!\n";
    }
}

/**
 * @var MyClass $myClass
 */
$myClass = deferrable(MyClass::class, ...$somethingArguments);
$myClass->doSomething1();
$myClass->doSomething2();
```

It will show as below:

```
One!
Two!
There!
Wanwan!
NyanNyan!
```

## Deferrable function
You can pass a function into the deferrable.

```php
use function PHPDeferrable\defer;
use function PHPDeferrable\deferrable;

deferrable(function () {
    defer(function () {
        echo "0: deferred call\n";
    });
    echo "0: first call\n";
});

deferrable(function () {
    defer(function () {
        echo "1: deferred call\n";
    });
    echo "1: first call\n";
});
```

It will show as below:

```
0: first call
0: deferred call
1: first call
1: deferred call
```

Deferrable function can be return a value.

```php
use function PHPDeferrable\defer;
use function PHPDeferrable\deferrable;

$result = deferrable(function () {
    defer(function () {
        // do something.
    });
    return "Return value\n";
});

echo $result;
```

It will show as below:
```
Return value
```

Deferrable can manipulate resource context.

```php
use function PHPDeferrable\defer;
use function PHPDeferrable\deferrable;

deferrable(function () {
    $handle = fopen('php://memory', 'r')
    defer(function () {
        fclose($handle)
    });
    // ... do something
});

```

## Context Manipulator
The context manipulator is very simple deferrable functions manipulator.
You can take possible to decreasing memory usage with using it.
`Defer::createContext` is using only to method of a class.
It is not required wrapping with `deferrable` function for you wanting to deferring a class. 

```php
class MyClass
{
    public function doSomething()
    {
        $context = Defer::createContext();
        $context->defer(function () {
            echo "Two!";
        });
        echo "One!";
    }
}

$myClass = new MyClass();
$myClass->doSomething();
```

## Notice
This library register global variables with `__temp_defers__`.
This variable cannot be changed at this time.
If you want to use this library without being bound by global variables, you must use `Defer::createContext`.

## License
MIT
