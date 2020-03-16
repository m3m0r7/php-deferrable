<?php declare(strict_types=1);

namespace PHPDeferrable;

use SplStack;

class DeferContext
{
    /**
     * @var SplStack|null
     */
    protected $splStack;

    /**
     * @var callable[]
     */
    protected $beforeCallbacks = [];

    /**
     * @var callable[]
     */
    protected $afterCallbacks = [];

    /**
     * @var callable[]
     */
    protected $everyBeforeCallbacks = [];

    /**
     * @var callable[]
     */
    protected $everyAfterCallbacks = [];

    /**
     * Create a defer instance.
     *
     * @param SplStack|null $splStack
     */
    public function __construct(?SplStack $splStack = null)
    {
        $this->splStack = $splStack ?? new SplStack();
    }

    public function __destruct()
    {
        $this->consume();
    }

    /**
     * Run post-processing for defer stacks.
     */
    public function consume()
    {
        foreach ($this->beforeCallbacks as $callback) {
            /**
             * @var callable $callback
             */
            $callback($this);
        }
        while ($callback = $this->pop()) {
            /**
             * @var DeferCallback $callback
             */
            foreach ($this->everyBeforeCallbacks as $everyCallback) {
                $everyCallback($this);
            }
            $callback();
            foreach ($this->everyAfterCallbacks as $everyCallback) {
                $everyCallback($this);
            }
        }
        foreach ($this->afterCallbacks as $callback) {
            /**
             * @var callable $callback
             */
            $callback($this);
        }
    }

    /**
     * Pop a callback from defer stacks.
     *
     * @return callable|false
     */
    public function pop()
    {
        if ($this->splStack->count() <= 0) {
            return false;
        }
        return $this->splStack->pop();
    }

    /**
     * Register a callback for deferring.
     *
     * @param callable $callback
     * @param mixed ...$arguments
     * @return DeferContext
     */
    public function defer(callable $callback, &...$arguments): self
    {
        $this->splStack->push(
            DeferCallback::factory(
                $callback,
                ...$arguments
            )
        );
        return $this;
    }

    /**
     * Run callbacks before calling a defer callback every time.
     *
     * @param callable $callback
     * @return DeferContext
     */
    public function everyBefore(callable $callback): self
    {
        $this->everyBeforeCallbacks[] = $callback;
        return $this;
    }

    /**
     * Run callbacks after calling a defer callback every time.
     *
     * @param callable $callback
     * @return DeferContext
     */
    public function everyAfter(callable $callback): self
    {
        $this->everyAfterCallbacks[] = $callback;
        return $this;
    }

    /**
     * Run callback once before calling a defer callback.
     *
     * @param callable $callback
     * @return DeferContext
     */
    public function before(callable $callback): self
    {
        $this->beforeCallbacks[] = $callback;
        return $this;
    }

    /**
     * Run callback once after calling a defer callback.
     *
     * @param callable $callback
     * @return DeferContext
     */
    public function after(callable $callback): self
    {
        $this->afterCallbacks[] = $callback;
        return $this;
    }
}
