<?php declare(strict_types=1);

namespace PHPDeferrable;

class DeferrableContinuableScope extends AbstractDeferrableScope
{
    public function getScopeType(): int
    {
        return DeferrableScopeType::CONTINUABLE;
    }
}
