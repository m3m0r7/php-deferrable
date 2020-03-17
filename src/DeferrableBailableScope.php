<?php declare(strict_types=1);

namespace PHPDeferrable;

class DeferrableBailableScope extends AbstractDeferrableScope
{
    public function getScopeType(): int
    {
        return DeferrableScopeType::BAILABLE;
    }
}
