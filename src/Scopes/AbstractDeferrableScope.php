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
     * @param callable|string $target
     * @return static
     */
    public static function of($target)
    {
        if (is_string($target)) {
            return new static($target, 'class');
        }
        return new static($target, 'callable');
    }

    public function getClassName(): string
    {
        if (!$this->isClass()) {
            throw new DeferrableException('The scope is not a class.');
        }
        return $this->target;
    }

    public function isClass(): bool
    {
        return $this->type === 'class';
    }

    abstract public function getScopeType(): int;

    public function invokeCallable(...$arguments)
    {
        if (!$this->isCallable()) {
            throw new DeferrableException('The scope is not a callable.');
        }
        return ($this->target)(...$arguments);
    }

    public function isCallable(): bool
    {
        return $this->type === 'callable';
    }
}
