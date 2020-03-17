<?php declare(strict_types=1);

namespace PHPDeferrable;

class DeferrableBailableScope extends AbstractDeferrableScope implements DeferrableScopeInterface
{
    public function getScopeType(): int
    {
        return DeferrableScopeType::BAILABLE;
    }
}
