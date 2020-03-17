<?php declare(strict_types=1);

namespace PHPDeferrable;

class DeferBailableScope extends AbstractDeferrableScope
{
    public function getScopeType(): int
    {
        return DeferrableScopeType::BAILABLE;
    }
}
