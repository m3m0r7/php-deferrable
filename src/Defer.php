<?php
namespace PHPDeferrable;

class Defer
{
    /**
     * Create a defer context.
     *
     * @return DeferContext
     */
    public static function createContext(): DeferContext
    {
        return new DeferContext(
            new \SplStack()
        );
    }
}
