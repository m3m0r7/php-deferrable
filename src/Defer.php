<?php declare(strict_types=1);

namespace PHPDeferrable;

use SplStack;

class Defer
{
    /**
     * Create a defer context.
     *
     * @param int $scopeType
     * @return DeferContext
     */
    public static function createContext(int $scopeType = DeferrableScopeType::CONTINUABLE): DeferContext
    {
        return new DeferContext(
            $scopeType,
            new SplStack()
        );
    }
}
