<?php declare(strict_types=1);

namespace PHPDeferrable;

interface DeferrableInterface
{
    public function isDeferrable(): bool;
}