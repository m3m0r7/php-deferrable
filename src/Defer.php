<?php
namespace PHPDeferrable;

class Defer
{
    public static function createContext(): DeferContext
    {
        return new DeferContext(
            new \SplStack()
        );
    }
}
