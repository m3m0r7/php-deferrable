<?php declare(strict_types=1);

namespace PHPDeferrable\Test;

use PHPDeferrable\Defer;
use PHPDeferrable\DeferBailableExceptionInterface;
use PHPDeferrable\DeferrableScopeType;
use PHPDeferrable\MergedDeferException;
use PHPUnit\Framework\TestCase;

class TestingContextException extends \Exception
{

}

class BailableTestingContextException extends \Exception implements DeferBailableExceptionInterface
{

}

class DeferCreateContextClassTestTestMyClass
{
    public function doSomething1()
    {
        $context = Defer::createContext();
        $context->defer(function () {
            echo "Two!\n";
        });

        $context->defer(function () {
            echo "Three!\n";
        });
        echo "One!\n";
    }

    public function doSomething2()
    {
        $context = Defer::createContext();
        $context->defer(function () {
            echo "NyanNyan!\n";
        });
        echo "Wanwan!\n";
    }

    public function doSomething3()
    {
        $context = Defer::createContext();
        $handle = fopen('php://memory', 'r');
        $context->defer(function () use ($handle) {
            fclose($handle);
        });
        return 'Return value';
    }

    public function doSomething4()
    {
        $context = Defer::createContext(DeferrableScopeType::BAILABLE);
        $context->defer(function () {
            throw new \Exception('exception 2');
        });

        $context->defer(function () {
            throw new TestingContextException('exception 2');
        });
        return 'Return value';
    }

    public function doSomething5()
    {
        $context = Defer::createContext();
        $context->defer(function () {
            throw new \Exception('exception 2');
        });

        $context->defer(function () {
            throw new TestingContextException('exception 2');
        });
        return 'Return value';
    }

    public function doSomething6()
    {
        $context = Defer::createContext();
        $context->defer(function () {
            throw new \Exception('exception 2');
        });

        $context->defer(function () {
            throw new BailableTestingContextException('exception 2');
        });
        return 'Return value';
    }
}

class DeferCreateContextClassTest extends TestCase
{
    public function testDeferPattern1()
    {
        ob_start();
        $myClass = new DeferCreateContextClassTestTestMyClass();
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
        $myClass = new DeferCreateContextClassTestTestMyClass();
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
        $myClass = new DeferCreateContextClassTestTestMyClass();
        $myClass->doSomething1();
        $result = ob_get_clean();

        $this->assertSame(
            "One!\nThree!\nTwo!\n",
            $result
        );
    }

    public function testDeferPattern4()
    {
        $myClass = new DeferCreateContextClassTestTestMyClass();
        $result = $myClass->doSomething3();

        $this->assertSame(
            "Return value",
            $result
        );
    }

    public function testDeferPattern5()
    {
        $myClass = new DeferCreateContextClassTestTestMyClass();
        $this->expectException(TestingContextException::class);
        $myClass->doSomething4();
    }

    public function testDeferPattern6()
    {
        $myClass = new DeferCreateContextClassTestTestMyClass();
        $this->expectException(MergedDeferException::class);
        $myClass->doSomething5();
    }

    public function testDeferPattern7()
    {
        $myClass = new DeferCreateContextClassTestTestMyClass();
        $this->expectException(BailableTestingContextException::class);
        $myClass->doSomething6();
    }
}
