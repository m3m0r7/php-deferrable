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
})();

deferrable(function () {
    defer(function () {
        echo "1: deferred call\n";
    });
    echo "1: first call\n";
})();
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
})();

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
    defer(function () use ($handle) {
        fclose($handle)
    });
    // ... do something
})();

```

`defer` can be passed any parameters and it will copy based the context.

```php
use function PHPDeferrable\defer;
use function PHPDeferrable\deferrable;

deferrable(function () {
    $message = 'Hello World';
    defer(function ($message) {
        echo $message;
    }, $message);
    // ... do something
})();

```

It will show as below:
```
Hello World
```

And it can be changed the parameter value in `defer` function with reference.

```php
use function PHPDeferrable\defer;
use function PHPDeferrable\deferrable;

deferrable(function () {
    $message = 'Hello World';
    defer(function (&$message) {
        echo $message;
    }, $message);

    defer(function (&$message) {
        $message = 'The cat has big power.';
    }, $message);
    // ... do something
})();

```

It will show as below:
```
The cat has big power.
```


## Exception of defer
Normally, php-deferrable is designed so that even if an exception is thrown in the defer, the processing of the stacked defer continues.
This is to resolve the inconsistency that Go has no exceptions, but PHP does.

```php
deferrable(function() {
    defer(function () {
        throw new Exception('exception 1');
    });

    defer(function () {
        throw new Exception('exception 2');
    });

    defer(function () {
        throw new Exception('exception 3');
    });
})()
```

In the case of the above example, all exceptions are combined and returned as `MergedDeferringException`.

However, you may want to stop if an exception occurs. Of course, such means are also available.
If an exception occurs, there are two ways to suspend defer processing.

The first uses `DeferBailableScope :: of` to return the current deferrable scope itself if an exception occurs.

```php
deferrable(
    DeferBailableScope::of(function() {
        defer(function () {
            throw new ThirdException('exception 1');
        });
    
        defer(function () {
            throw new SecondException('exception 2');
        });
    
        defer(function () {
            throw new FirstException('exception 3');
        });
    )
})()
```

In this case, `FirstException` is thrown as an exception to the outer scope. The reason `FirstException` is thrown is
The defer process pops the stack. In other words, the process is started from the last registered defer.
Also, in contrast to `DeferBailableScope`, if you want to explicitly specify an exception that can be continued, use` DeferContinuableScope`.

The second is to throw an exception that inherits from `DeferBailableExceptionInterface`.
If you inherit from this interface, stop merging exceptions at that point and return only those inherited exceptions.

```php

class SecondException extends \Exception implements DeferBailableExceptionInterface
{
} 

deferrable(function() {
    defer(function () {
        throw new ThirdException('exception 1');
    });

    defer(function () {
        throw new SecondException('exception 2');
    });

    defer(function () {
        throw new FirstException('exception 3');
    });
})()
```

In the above case, a `SecondException` is thrown.
In the case of `Defer :: createContext`, it can be controlled by passing the scope type as the first argument.

```php
class Example 
{
    public function doSomething()
    {
        $context = Defer::createContext(DeferrableScopeType::BAILABLE);
    
        $context->defer(function () {
            throw new ThirdException('exception 1');
        });
    
        $context->defer(function () {
            throw new SecondException('exception 2');
        });
    
        $context->defer(function () {
            throw new FirstException('exception 3');
        });
    }
}

(new Example())->doSomething();
```


In the above case, `FirstException` is thrown as an exception.


## Context Manipulator
The context manipulator is very simple deferrable functions manipulator.
You can take possible to decreasing memory usage with using it.
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

## License
MIT
