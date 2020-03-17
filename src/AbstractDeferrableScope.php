<?php declare(strict_types=1);

namespace PHPDeferrable;

abstract class AbstractDeferrableScope
{
    protected $targetClass;

    public function __construct(string $targetClass)
    {
        $this->targetClass = $targetClass;
    }

    public function getClassName(): string
    {
        return $this->targetClass;
    }

    public function getScopeType(): int
    {
        throw new DeferrableException(
            'Scope type is not defined'
        );
    }
}
