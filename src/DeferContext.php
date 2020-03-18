<?php declare(strict_types=1);

namespace PHPDeferrable;

use Exception;
use PHPDeferrable\Contracts\DeferBailableExceptionInterface;
use PHPDeferrable\Exceptions\DeferrableException;
use PHPDeferrable\Exceptions\MergedDeferringException;
use PHPDeferrable\Scopes\DeferrableScopeType;
use SplStack;
use Throwable;

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
     * @var Exception[] array
     */
    protected $exceptionStacks = [];

    /**
     * @var int $scopeType
     */
    protected $scopeType;

    /**
     * @var bool
     */
    protected $consumed = false;

    /**
     * Create a defer instance.
     *
     * @param int $scopeType
     * @param SplStack|null $splStack
     */
    public function __construct($scopeType, ?SplStack $splStack = null)
    {
        $this->scopeType = $scopeType;
        $this->splStack = $splStack ?? new SplStack();
    }

    public function __destruct()
    {
        if ($this->consumed) {
            return;
        }
        $this->consume();
    }

    /**
     * Run post-processing for defer stacks.
     *
     */
    public function consume()
    {
        try {
            $this->exceptionStacks = [];
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

                try {
                    $callback();
                } catch (Throwable $e) {
                    if ($e instanceof DeferBailableExceptionInterface) {
                        $this->exceptionStacks = [];
                        throw $e;
                    }
                    switch ($this->scopeType) {
                        case DeferrableScopeType::CONTINUABLE:
                            $this->exceptionStacks[] = $e;
                            break;
                        case DeferrableScopeType::BAILABLE:
                            $this->exceptionStacks = [];
                            throw $e;
                        default:
                            $this->exceptionStacks = [];
                            throw new DeferrableException(
                                'Specified scope type is invalid'
                            );
                    }
                }
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
            if (count($this->exceptionStacks) > 0) {
                $messages = '';
                foreach ($this->exceptionStacks as $number => $exceptionStack) {
                    $messages .= '[Exception ' . (++$number) . ']: ' . $exceptionStack->getMessage() . "\n" . $exceptionStack->getTraceAsString() . ' (line: ' . $exceptionStack->getLine() . ', file:' . $exceptionStack->getFile() . ')';
                }
                $this->exceptionStacks = [];
                throw new MergedDeferringException(
                    $messages
                );
            }
        } finally {
            $this->consumed = true;
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
