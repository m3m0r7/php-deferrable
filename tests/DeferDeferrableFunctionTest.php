<?php declare(strict_types=1);

namespace PHPDeferrable\Test;

use PHPUnit\Framework\TestCase;
use function PHPDeferrable\defer;
use function PHPDeferrable\deferrable;

class DeferDeferrableFunctionTest extends TestCase
{
    public function testDeferPattern1()
    {
        ob_start();
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

        $result = ob_get_clean();

        $this->assertSame(
            "0: first call\n0: deferred call\n1: first call\n1: deferred call\n",
            $result
        );
    }

    public function testDeferPattern2()
    {
        ob_start();
        deferrable(function () {
            defer(function () {
                echo "0: deferred call\n";
            });
            echo "0: first call\n";
        });

        $result = ob_get_clean();

        $this->assertSame(
            "0: first call\n0: deferred call\n",
            $result
        );
    }

    public function testDeferPattern3()
    {
        ob_start();
        deferrable(function () {
            defer(function () {
                echo "1: deferred call\n";
            });
            echo "1: first call\n";
        });

        $result = ob_get_clean();

        $this->assertSame(
            "1: first call\n1: deferred call\n",
            $result
        );
    }

    public function testDeferPattern4()
    {
        ob_start();
        deferrable(function () {
            defer(function () {
                echo "1: deferred call\n";
            });
            defer(function () {
                echo "1: deferred call2\n";
            });
            echo "1: first call\n";
        });

        $result = ob_get_clean();

        $this->assertSame(
            "1: first call\n1: deferred call2\n1: deferred call\n",
            $result
        );
    }

    public function testDeferPattern5()
    {
        $result = deferrable(function () {
            defer(function () {
                // do something
            });
            return 'Return value';
        });

        $this->assertSame(
            "Return value",
            $result
        );
    }

    public function testDeferPattern6()
    {
        $result = deferrable(function () {
            $handle = fopen('php://memory', 'r');
            defer(function () use ($handle) {
                fclose($handle);
            });
            return 'Return value';
        });

        $this->assertSame(
            "Return value",
            $result
        );
    }
}
