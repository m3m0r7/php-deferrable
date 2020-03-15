<?php
namespace PHPDeferrable;

class DeferContext
{
    protected $splStack;
    protected $beforeCallbacks = [];
    protected $afterCallbacks = [];
    protected $everyBeforeCallbacks = [];
    protected $everyAfterCallbacks = [];

    public function __construct(?\SplStack $splStack = null)
    {
        $this->splStack = $splStack ?? new \SplStack();
    }

    public function pop()
    {
        return $this->splStack->pop();
    }

    public function __destruct()
    {
        foreach ($this->beforeCallbacks as $callback) {
            $callback($this);
        }
        try {
            while ($callback = $this->pop()) {
                foreach ($this->everyBeforeCallbacks as $everyCallback) {
                    $everyCallback($this);
                }
                $callback();
                foreach ($this->everyAfterCallbacks as $everyCallback) {
                    $everyCallback($this);
                }
            }
        } catch (\RuntimeException $e) {

        }
        foreach ($this->afterCallbacks as $callback) {
            $callback($this);
        }
    }

    public function defer(callable $callback): self
    {
        $this->splStack->push($callback);
        return $this;
    }

    public function everyBefore(callable $callback): self
    {
        $this->everyBeforeCallbacks[] = $callback;
        return $this;
    }

    public function everyAfter(callable $callback): self
    {
        $this->everyAfterCallbacks[] = $callback;
        return $this;
    }

    public function before(callable $callback): self
    {
        $this->beforeCallbacks[] = $callback;
        return $this;
    }

    public function after(callable $callback): self
    {
        $this->afterCallbacks[] = $callback;
        return $this;
    }
}
