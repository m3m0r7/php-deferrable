<?php declare(strict_types=1);

namespace PHPDeferrable\Scopes;

use PHPDeferrable\Contracts\DeferrableScopeInterface;

abstract class AbstractDeferrableScope implements DeferrableScopeInterface
{
    protected $target;
    protected $type;

    /**
     * @param mixed $target
     * @param string $type
     */
    protected function __construct($target, string $type)
    {
        $this->target = $target;
        $this->type;
    }

    /**
     * @param string $targetClass
     * @return static
     */
    public static function fromClassPath(string $targetClass)
    {
        return new static($targetClass, 'class');
    }

    /**
     * @param callable $targetCallable
     * @return static
     */
    public static function fromCallable(callable $targetCallable)
    {
        return new static($targetCallable, 'function');
    }

    public function getClassName(): string
    {
        return $this->target;
    }

    abstract public function getScopeType(): int;

    public function isClass(): bool
    {
        return $this->type === 'class';
    }

    public function isFunction(): bool
    {
        return $this->type === 'function';
    }
}
