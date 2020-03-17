<?php declare(strict_types=1);

namespace PHPDeferrable\Contracts;

interface DeferrableScopeInterface
{
    public function __construct(string $targetClass);

    public static function factory(string $targetClass);

    public function getClassName(): string;

    public function getScopeType(): int;
}
