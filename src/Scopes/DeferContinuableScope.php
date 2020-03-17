<?php declare(strict_types=1);

namespace PHPDeferrable\Scopes;

class DeferContinuableScope extends AbstractDeferrableScope
{
    public function getScopeType(): int
    {
        return DeferrableScopeType::CONTINUABLE;
    }
}
