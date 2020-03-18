# PHP-Deferrable - Simple and Powerful deferrable run code library

`PHP-Deferrable` はシンプルでかつ強力にあなたのコードを遅延処理することができるライブラリです。
このライブラリは Go の defer とよく似ています。
テスト以外に置いて他のライブラリに依存しないため、このライブラリはとてもシンプルなライブラリです。

## ドキュメント
- [English](./readme.md)
- 日本語 (現在)

## インストール

Composer を使用する:
```
composer require m3m0r7/php-deferrable
```

## 今までの課題
Go には defer があり、return が返る前に defer の中身を実行することが出来ます。
しかし PHP は defer がないものの、 `try-finally` やデストラクタの破棄タイミングを用いて defer を実現することが可能です。

```php
try {
    // ... do something
} finally {
    // 後処理
}
```

これにはいくつか問題があり、後処理をするコードが煩雑になる可能性と、 `try` 構文が長くなりすぎてしまうと、何を処理するのかわからなくなってしまいます。
そして、不要なインデントに苛まれることでしょう。

`php-deferrable` はその課題を解決するため、非常にシンプルな関数とクラスを提供することにより、それらすべての問題を解決します。 


## クイックスタート
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

上記は下記のように表示されます。

```
One!
Two!
There!
Wanwan!
NyanNyan!
```

## Deferrable 関数
`deferrable` 関数に callable なパラメータを渡すことも可能です。 

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
})();
```

下記のように表示されます。

```
0: first call
0: deferred call
1: first call
1: deferred call
```

`deferrable` 関数は関数の実行結句を返り値として返すことも可能です。

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

上記は以下のようになります。
```
Return value
```

`deferrable` は resource の後処理などにも有用です。

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

`defer` は可変長引数を用いてパラメータを渡すことも可能です。パラメータはコンテキストに準じてコピーされます。


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

上記は下記のようになります。
```
Hello World
```

また、リファレンスとすることにより、 `defer` 内でパラメータの値を変更させることも可能です。

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

上記は下記のようになります。
```
The cat has big power.
```

## defer の例外
通常 php-deferrable は defer 内で例外が投げられてもスタックされた defer の処理は継続して行うように設計されています。
これは Go には例外はなく、しかし PHP に例外があるという矛盾を解決するためです。

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

上記のような例の場合、例外はすべて結合され `MergedDeferringException` として返却します。

しかし、例外が発生した場合止めたい場合もあるでしょう。そのような手段ももちろん用意してあります。
例外が発生した場合、defer の処理を中断する方法は二通りあります。

1 つ目は現在の deferrable スコープそのものを例外が発生した場合返すように `DeferBailableScope::of` を使用します。

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

この場合、 `FirstException` が例外として外のスコープに投げられます。`FirstException` が投げられる理由は、
defer の処理はスタックをポップしていきます。つまり、一番最後に登録された defer から処理をすることになります。
また、 `DeferBailableScope` とは反対に、継続できる例外を明示的に指定したい場合、 `DeferContinuableScope` を使用します。

2 つ目は `DeferBailableExceptionInterface` を継承した例外を投げる方法です。
このインタフェースを継承している場合、その時点で例外のマージを止めて、その継承された例外のみを返します。


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

上記の場合、`SecondException` が投げられます。

`Defer::createContext` の場合、第一引数にスコープの種類を渡すことにより制御可能です。

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

上記の場合 `FirstException` が例外として投げられます。 

## コンテキストマニピュレータ
コンテキストマニピュレータは非常にシンプルな方法で遅延処理を実現しています。
この方法を使うことにより defer と deferrable との実行と比較して PHP における無駄なスタックやメモリ使用量を控えることが出来ます。
これを使うことによりクラス名を `deferrable` でラップする必要がなくなります。 

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

## ライセンス
MIT
