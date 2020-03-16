<?php declare(strict_types=1);

namespace PHPDeferrable;

class DeferCallback
{
    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var array|mixed[]
     */
    protected $arguments;

    /**
     * @param callable $callback
     * @param mixed ...$arguments
     * @return static
     */
    public static function factory(callable $callback, &...$arguments)
    {
        return new static($callback, ...$arguments);
    }

    /**
     * DeferCallback constructor.
     * @param callable $callback
     * @param mixed ...$arguments
     */
    public function __construct(callable $callback, &...$arguments)
    {
        $this->callback = $callback;
        $this->arguments = $arguments;
    }

    public function __invoke()
    {
        return ($this->callback)(...$this->arguments);
    }
}
