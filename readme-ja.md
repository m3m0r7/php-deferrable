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


## コンテキストマニピュレータ
コンテキストマニピュレータは非常にシンプルな方法で遅延処理を実現しています。
この方法を使うことにより defer と deferrable との実行と比較して PHP における無駄なスタックやメモリ使用量を控えることが出来ます。
`Defer::createContext` を使用することにより可能であり、これはクラス内のメソッドにのみ使用可能です。
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

## 注意事項
このライブラリは `__temp_defers__` という名前のグローバル変数を生成します。
このグローバル変数の名称は現状変更することは出来ません。（変更するためのメリットも感じていませし、これが生えることによって困る場合は素直に `Defer::createContext` を使うことをおすすめします）

## ライセンス
MIT
