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
});
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
});

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
});

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
});

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
});

```

上記は下記のようになります。
```
The cat has big power.
```


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
