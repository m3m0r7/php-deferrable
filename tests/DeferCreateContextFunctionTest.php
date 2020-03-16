<?php declare(strict_types=1);

namespace PHPDeferrable\Test;
use PHPDeferrable\Defer;
use PHPUnit\Framework\TestCase;

class DeferCreateContextFunctionTest extends TestCase
{
    public function testDeferPattern1()
    {
        ob_start();
        $a = function () {
            $context = Defer::createContext();
            $context->defer(function () {
                echo "0: deferred call\n";
            });
            echo "0: first call\n";
        };

        $b = function () {
            $context = Defer::createContext();
            $context->defer(function () {
                echo "1: deferred call\n";
            });
            echo "1: first call\n";
        };

        $a();
        $b();

        $result = ob_get_clean();

        $this->assertSame(
            "0: first call\n0: deferred call\n1: first call\n1: deferred call\n",
            $result
        );
    }

    public function testDeferPattern2()
    {
        ob_start();
        $a = function () {
            $context = Defer::createContext();
            $context->defer(function () {
                echo "0: deferred call\n";
            });
            echo "0: first call\n";
        };

        $a();
        $result = ob_get_clean();

        $this->assertSame(
            "0: first call\n0: deferred call\n",
            $result
        );
    }

    public function testDeferPattern3()
    {
        ob_start();
        $b = function () {
            $context = Defer::createContext();
            $context->defer(function () {
                echo "1: deferred call\n";
            });
            echo "1: first call\n";
        };
        $b();

        $result = ob_get_clean();

        $this->assertSame(
            "1: first call\n1: deferred call\n",
            $result
        );
    }

    public function testDeferPattern4()
    {
        ob_start();
        $b = function () {
            $context = Defer::createContext();
            $context->defer(function () {
                echo "1: deferred call\n";
            });
            $context->defer(function () {
                echo "1: deferred call2\n";
            });
            echo "1: first call\n";
        };
        $b();

        $result = ob_get_clean();


        $this->assertSame(
            "1: first call\n1: deferred call2\n1: deferred call\n",
            $result
        );
    }

}
