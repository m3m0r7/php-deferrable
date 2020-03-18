<?php declare(strict_types=1);

namespace PHPDeferrable\Contracts;

interface DeferrableScopeInterface
{
    public static function of($target);

    public function getClassName(): string;

    public function getScopeType(): int;

    public function isClass(): bool;

    public function isCallable(): bool;

    public function invokeCallable(...$arguments);
}
