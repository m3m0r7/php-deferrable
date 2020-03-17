<?php declare(strict_types=1);

namespace PHPDeferrable\Test;

use PHPDeferrable\Contracts\DeferBailableExceptionInterface;
use PHPDeferrable\Exceptions\MergedDeferException;
use PHPDeferrable\Scopes\DeferBailableScope;
use PHPUnit\Framework\TestCase;
use function PHPDeferrable\defer;
use function PHPDeferrable\deferrable;

class TestingException extends \Exception
{

}

class BailableTestingException extends \Exception implements DeferBailableExceptionInterface
{

}

class DeferDeferrableClassTestTestMyClass
{
    public function doSomething1()
    {
        defer(function () {
            echo "Two!\n";
        });

        defer(function () {
            echo "Three!\n";
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

    public function doSomething3(): string
    {
        defer(function () {
        });
        return "Return value";
    }

    public function doSomething4(): string
    {
        $handle = fopen('php://memory', 'r');
        defer(function () use ($handle) {
            fclose($handle);
        });
        return "Return value";
    }

    public function doSomething6(): string
    {
        $message = 'Test';
        defer(function (&$message) {
            $message = 'Test2';
        }, $message);

        return $message;
    }

    public function doSomething7()
    {
        $message = 'Test';
        defer(function (&$message) {
            echo $message;
        }, $message);

        defer(function (&$message) {
            $message = 'Test2';
        }, $message);

    }

    public function doSomething8()
    {
        $message = 'Test';
        defer(function () use (&$message) {
            echo $message;
        });

        defer(function () use (&$message) {
            $message = 'Test2';
        });
    }

    public function doSomething9()
    {
        $message = 'Test';
        defer(function ($message) {
            echo $message;
        }, $message);

        defer(function ($message) {
            $message = 'Test2';
        }, $message);
    }

    public function doSomething10()
    {
        defer(function () {
           throw new \RuntimeException('exception test 3');
        });

        defer(function () {
            throw new \Exception('exception test 2');
        });

        defer(function () {
            throw new TestingException('exception test 1');
        });

    }

    public function doSomething11(string $a, string $b)
    {
        defer(function () use ($a, $b) {
            echo $a . $b;
        });
    }

    public function doSomething12()
    {
        defer(function () {
            throw new TestingException('exception test 3');
        });

        defer(function () {
            throw new BailableTestingException('exception test 2');
        });

        defer(function () {
            throw new \Exception('exception test 1');
        });
    }
}

class DeferDeferrableClassTest extends TestCase
{
    public function testDeferPattern1()
    {
        ob_start();
        /**
         * @var DeferDeferrableClassTestTestMyClass $myClass
         */
        $myClass = deferrable(DeferDeferrableClassTestTestMyClass::class);
        $myClass->doSomething1();
        $myClass->doSomething2();
        $result = ob_get_clean();

        $this->assertSame(
            "One!\nThree!\nTwo!\nWanwan!\nNyanNyan!\n",
            $result
        );
    }

    public function testDeferPattern2()
    {
        ob_start();
        /**
         * @var DeferDeferrableClassTestTestMyClass $myClass
         */
        $myClass = deferrable(DeferDeferrableClassTestTestMyClass::class);
        $myClass->doSomething2();
        $result = ob_get_clean();

        $this->assertSame(
            "Wanwan!\nNyanNyan!\n",
            $result
        );
    }

    public function testDeferPattern3()
    {
        ob_start();
        /**
         * @var DeferDeferrableClassTestTestMyClass $myClass
         */
        $myClass = deferrable(DeferDeferrableClassTestTestMyClass::class);
        $myClass->doSomething1();
        $result = ob_get_clean();

        $this->assertSame(
            "One!\nThree!\nTwo!\n",
            $result
        );
    }

    public function testDeferPattern4()
    {
        /**
         * @var DeferDeferrableClassTestTestMyClass $myClass
         */
        $myClass = deferrable(DeferDeferrableClassTestTestMyClass::class);
        $result = $myClass->doSomething3();

        $this->assertSame(
            "Return value",
            $result
        );
    }

    public function testDeferPattern5()
    {
        /**
         * @var DeferDeferrableClassTestTestMyClass $myClass
         */
        $myClass = deferrable(DeferDeferrableClassTestTestMyClass::class);
        $result = $myClass->doSomething4();

        $this->assertSame(
            "Return value",
            $result
        );
    }

    public function testDeferPattern6()
    {
        /**
         * @var DeferDeferrableClassTestTestMyClass $myClass
         */
        $myClass = deferrable(DeferDeferrableClassTestTestMyClass::class);
        $result = $myClass->doSomething6();

        $this->assertSame(
            "Test",
            $result
        );
    }

    public function testDeferPattern7()
    {
        /**
         * @var DeferDeferrableClassTestTestMyClass $myClass
         */
        $myClass = deferrable(DeferDeferrableClassTestTestMyClass::class);

        ob_start();
        $myClass->doSomething7();
        $result = ob_get_clean();

        $this->assertSame(
            "Test2",
            $result
        );
    }

    public function testDeferPattern8()
    {
        /**
         * @var DeferDeferrableClassTestTestMyClass $myClass
         */
        $myClass = deferrable(DeferDeferrableClassTestTestMyClass::class);

        ob_start();
        $myClass->doSomething8();
        $result = ob_get_clean();

        $this->assertSame(
            "Test2",
            $result
        );
    }

    public function testDeferPattern9()
    {
        /**
         * @var DeferDeferrableClassTestTestMyClass $myClass
         */
        $myClass = deferrable(DeferDeferrableClassTestTestMyClass::class);

        ob_start();
        $myClass->doSomething9();
        $result = ob_get_clean();

        $this->assertSame(
            "Test",
            $result
        );
    }

    public function testDeferPattern10()
    {
        /**
         * @var DeferDeferrableClassTestTestMyClass $myClass
         */
        $myClass = deferrable(
            DeferBailableScope::fromClassPath(
                DeferDeferrableClassTestTestMyClass::class
            )
        );

        $this->expectException(TestingException::class);

        $myClass->doSomething10();
    }

    public function testDeferPattern11()
    {
        /**
         * @var DeferDeferrableClassTestTestMyClass $myClass
         */
        $myClass = deferrable(
            DeferDeferrableClassTestTestMyClass::class
        );

        $this->expectException(MergedDeferException::class);

        $myClass->doSomething10();
    }

    public function testDeferPattern12()
    {
        /**
         * @var DeferDeferrableClassTestTestMyClass $myClass
         */
        $myClass = deferrable(
            DeferDeferrableClassTestTestMyClass::class
        );

        ob_start();
        $myClass->doSomething11('Hello', 'World');
        $result = ob_get_clean();

        $this->assertSame(
            "HelloWorld",
            $result
        );
    }

    public function testDeferPattern13()
    {
        /**
         * @var DeferDeferrableClassTestTestMyClass $myClass
         */
        $myClass = deferrable(
            DeferDeferrableClassTestTestMyClass::class
        );

        $this->expectException(BailableTestingException::class);

        $myClass->doSomething12();
    }

}
