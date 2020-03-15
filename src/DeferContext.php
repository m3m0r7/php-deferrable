<?php
namespace PHPDeferrable;

class DeferContext
{
    protected $splStack;

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
        try {
            while ($callback = $this->splStack->pop()) {
                $callback();
            }
        } catch (\RuntimeException $e) {

        }
    }

    public function defer(callable $callback): self
    {
        $this->splStack->push($callback);
        return $this;
    }
}
