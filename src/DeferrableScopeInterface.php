<?php declare(strict_types=1);

namespace PHPDeferrable;

interface DeferrableScopeInterface
{
    public function __construct(string $targetClass);
    public function getClassName(): string;
    public function getScopeType(): int;
}
