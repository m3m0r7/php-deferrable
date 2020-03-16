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
