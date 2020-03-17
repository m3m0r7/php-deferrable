<?php declare(strict_types=1);

namespace PHPDeferrable;

class DeferContinuableScope extends AbstractDeferrableScope
{
    public function getScopeType(): int
    {
        return DeferrableScopeType::CONTINUABLE;
    }
}
