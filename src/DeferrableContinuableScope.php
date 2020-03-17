<?php declare(strict_types=1);

namespace PHPDeferrable;

class DeferrableContinuableScope extends AbstractDeferrableScope implements DeferrableScopeInterface
{
    public function getScopeType(): int
    {
        return DeferrableScopeType::CONTINUABLE;
    }
}
