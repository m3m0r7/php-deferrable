<?php declare(strict_types=1);

namespace PHPDeferrable;

abstract class AbstractDeferrableScope implements DeferrableScopeInterface
{
    protected $targetClass;

    /**
     * @param string $targetClass
     * @return static
     */
    public static function factory(string $targetClass)
    {
        return new static($targetClass);
    }

    /**
     * @param string $targetClass
     */
    public function __construct(string $targetClass)
    {
        $this->targetClass = $targetClass;
    }

    public function getClassName(): string
    {
        return $this->targetClass;
    }

    abstract public function getScopeType(): int;
}
