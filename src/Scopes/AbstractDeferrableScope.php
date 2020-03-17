<?php declare(strict_types=1);

namespace PHPDeferrable\Scopes;

use PHPDeferrable\Contracts\DeferrableScopeInterface;
use PHPDeferrable\Exceptions\DeferrableException;

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
        $this->type = $type;
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
        return new static($targetCallable, 'callable');
    }

    public function getClassName(): string
    {
        if (!$this->isClass()) {
            throw new DeferrableException('The scope is not a class.');
        }
        return $this->target;
    }

    abstract public function getScopeType(): int;

    public function isClass(): bool
    {
        return $this->type === 'class';
    }

    public function isCallable(): bool
    {
        return $this->type === 'callable';
    }

    public function invokeCallable(...$arguments)
    {
        if (!$this->isCallable()) {
            throw new DeferrableException('The scope is not a callable.');
        }
        return ($this->target)(...$arguments);
    }
}
