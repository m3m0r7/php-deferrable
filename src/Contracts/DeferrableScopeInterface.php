<?php declare(strict_types=1);

namespace PHPDeferrable\Contracts;

interface DeferrableScopeInterface
{
    public static function fromClassPath(string $targetClass);

    public static function fromCallable(callable $targetCallable);

    public function getClassName(): string;

    public function getScopeType(): int;

    public function isClass(): bool;

    public function isCallable(): bool;

    public function invokeCallable(...$arguments);
}
