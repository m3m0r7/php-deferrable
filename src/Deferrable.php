<?php declare(strict_types=1);

namespace PHPDeferrable;

class Deferrable
{
    /**
     * @var DeferContext|null
     */
    protected static $currentContext;

    /**
     * @var int
     */
    protected static $temporaryClassCounter = 0;

    /**
     * Remove current context.
     */
    public static function removeContext()
    {
        static::$currentContext = null;
    }

    /**
     * @return DeferContext
     */
    public static function getCurrentContext()
    {
        static $anonymousContext;
        if (!$anonymousContext) {
            $anonymousContext = new DeferContext();
        }
        return static::$currentContext ?? $anonymousContext;
    }

    /**
     * @param string|null $className
     * @param string|null $methodName
     * @return DeferContext
     */
    public static function createDeferContext(?string $className = null, ?string $methodName = null): DeferContext
    {
        return static::getCurrentContext() ?? new DeferContext();
    }

    /**
     * Consume deferred stacks.
     *
     * @param DeferContext $context
     * @param string|null $className
     * @param string|null $methodName
     */
    public static function consumeDefers(DeferContext $context): void
    {
        $context->consume();
    }

    /**
     * @param callable $deferrableFunction
     * @param mixed ...$arguments pass parameters into a function
     * @return mixed
     */
    protected static function makeFunctionContextManipulator(callable $deferrableFunction, ...$arguments)
    {
        $context = static::createDeferContext(null, null);
        try {
            $result = $deferrableFunction(...$arguments);
        } finally {
            static::consumeDefers($context, null, null);
            static::removeContext();
        }
        return $result;
    }

    /**
     * Allows to defer the specified function or class
     *
     * @param callable|string $targetClass callable or class path
     * @param mixed ...$arguments pass parameters into class constructor
     *
     * @return DeferrableInterface|mixed
     * @throws \ReflectionException
     */
    public static function makeContextManipulator($targetClass, ...$arguments)
    {
        if (is_callable($targetClass)) {
            return static::makeFunctionContextManipulator($targetClass, ...$arguments);
        }

        $reflection = new \ReflectionClass($targetClass);
        $body = [
            'public function isDeferrable(): bool { return true; }',
        ];

        $makeModifier = function (\ReflectionMethod $method): string {
            $modifier = [];
            if ($method->isProtected()) {
                $modifier[] = 'protected';
            }

            if ($method->isPrivate()) {
                $modifier[] = 'private';
            }

            if ($method->isStatic()) {
                $modifier[] = 'static';
            }

            if ($method->isPublic()) {
                $modifier[] = 'public';
            }

            if ($method->isFinal()) {
                throw new DeferrableException(
                    'deferrable cannot wrap `' . $method->getName() . '` because it is including `final` modifier. ' .
                    'Please remove `final` modifier or use `Defer::createContext` instead of deferrable and defer functions.'
                );
            }

            return implode(' ', $modifier);
        };

        foreach ($reflection->getMethods() as $method) {
            $methodName = $method->getName();
            if ($method->isAbstract()) {
                continue;
            }
            $signature = '';
            if ($method->getReturnType()) {
                $returnType = $method->getReturnType();
                $signature = $returnType->getName();
                if ($returnType->allowsNull()) {
                    $signature = '?' . $signature;
                }
                $signature = ': ' . $signature;
            }

            $body[] = $makeModifier($method) . ' function ' . $methodName . '()' . $signature . ' { try{ '
                . '$deferContext = \\' . __NAMESPACE__ . '\\Deferrable::createDeferContext(__CLASS__, __METHOD__); '
                . '$result = parent::' . $methodName . '(...func_get_args()); '
                . '} finally {'
                . '\\' . __NAMESPACE__ . '\\Deferrable::consumeDefers($deferContext);'
                . '\\' . __NAMESPACE__ . '\\Deferrable::removeContext();'
                . '}'
                . 'return $result; '
                . '}';
        }

        $temporaryClassName = Runtime::DEFER_ANONYMOUS_CLASS_PREFIX . (static::$temporaryClassCounter++);
        eval('class ' . $temporaryClassName . ' extends ' . $targetClass . ' implements \\' . __NAMESPACE__ . '\\DeferrableInterface { ' . implode($body) . ' }');
        return new $temporaryClassName(...$arguments);
    }


    /**
     * Register a callback for deferring.
     *
     * @param callable $callback
     * @param mixed ...$arguments
     */
    public static function defer(callable $callback, &...$arguments): void
    {
        /**
         * @var string $currentStackName
         */
        static::getCurrentContext()->defer(
            $callback,
            ...$arguments
        );
    }

}