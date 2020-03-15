<?php declare(strict_types=1);

namespace PHPDeferrable\Test;
use PHPUnit\Framework\TestCase;

use function PHPDeferrable\defer;
use function PHPDeferrable\deferrable;

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
}
