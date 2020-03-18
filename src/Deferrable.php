<?php declare(strict_types=1);

namespace PHPDeferrable;

use PHPDeferrable\Contracts\DeferrableInterface;
use PHPDeferrable\Contracts\DeferrableScopeInterface;
use PHPDeferrable\Exceptions\DeferrableException;
use PHPDeferrable\Scopes\DeferContinuableScope;
use PHPDeferrable\Scopes\DeferrableScopeType;
use ReflectionMethod;

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
     * @var int
     */
    protected static $scopeType = DeferrableScopeType::CONTINUABLE;

    /**
     * @var int
     */
    protected static $defaultScopeType = DeferrableScopeType::CONTINUABLE;


    /**
     * Allows to defer the specified function or class
     *
     * @param callable|string|DeferrableScopeInterface $targetClass callable or class path
     * @param mixed ...$arguments pass parameters into class constructor
     *
     * @return DeferrableInterface|mixed
     * @throws \ReflectionException
     */
    public static function makeContextManipulator($targetClass, ...$arguments)
    {
        if (is_callable($targetClass)) {
            return static::makeFunctionContextManipulator(
                $targetClass,
                ...$arguments
            );
        }

        $scope = null;
        if (is_string($targetClass)) {
            $scope = DeferContinuableScope::of($targetClass);
        } else if ($targetClass instanceof DeferrableScopeInterface) {
            $scope = $targetClass;
        } else {
            throw new DeferrableException(
                'Passed parameter is invalid'
            );
        }

        if ($scope->isCallable()) {
            return static::makeFunctionContextManipulator(
                $scope,
                ...$arguments
            );
        }

        $reflection = new \ReflectionClass(
            $scope->getClassName()
        );
        $body = [];

        foreach ($reflection->getMethods() as $method) {
            $methodName = $method->getName();
            if ($method->isAbstract()) {
                continue;
            }
            $body[] = static::makeMethodSignature($method) . ' { '
                . '$deferContext = \\' . __NAMESPACE__ . '\\Deferrable::createDeferContext(' . $scope->getScopeType() . '); '
                . 'try{'
                . '$result = parent::' . $methodName . '(...func_get_args()); '
                . '} finally {'
                . '\\' . __NAMESPACE__ . '\\Deferrable::consume($deferContext);'
                . '}'
                . 'return $result; '
                . '}';
        }

        $temporaryClassName = Runtime::DEFER_ANONYMOUS_CLASS_PREFIX . (static::$temporaryClassCounter++);

        eval(
            'class ' . $temporaryClassName . ' extends ' . $scope->getClassName() . ' implements \\' . __NAMESPACE__ . '\\Contracts\\DeferrableInterface'
            . '{'
            . implode($body)
            . '}'
        );

        return new $temporaryClassName(...$arguments);
    }

    /**
     * @param callable|DeferrableScopeInterface $deferrableFunction
     * @param mixed ...$arguments pass parameters into a function
     * @return mixed
     */
    protected static function makeFunctionContextManipulator($deferrableFunction, ...$arguments)
    {
        $context = static::createDeferContext(
            ($deferrableFunction instanceof DeferrableScopeInterface)
                ? $deferrableFunction->getScopeType()
                : static::$defaultScopeType
        );
        try {
            $result = $deferrableFunction instanceof DeferrableScopeInterface
                ? $deferrableFunction->invokeCallable(...$arguments)
                : $deferrableFunction(...$arguments);
        } finally {
            static::consume($context);
        }
        return $result;
    }

    /**
     * @param int $scopeType
     * @return DeferContext
     */
    public static function createDeferContext(int $scopeType): DeferContext
    {
        static::$scopeType = $scopeType;
        return static::$currentContext = new DeferContext(static::$scopeType);
    }

    /**
     * Consume deferred stacks.
     *
     * @param DeferContext $context
     */
    public static function consume(DeferContext $context): void
    {
        static::$scopeType = static::$defaultScopeType;

        try {
            $context->consume();
        } finally {
            static::removeContext();
        }
    }

    /**
     * Remove current context.
     */
    public static function removeContext()
    {
        static::$currentContext = null;
    }

    /**
     * @param ReflectionMethod $method
     * @return string
     */
    protected static function makeMethodSignature(ReflectionMethod $method): string
    {
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

        $returnType = '';
        if ($method->getReturnType()) {
            $returnTypeObject = $method->getReturnType();
            $returnType = $returnTypeObject->getName();
            if ($returnTypeObject->allowsNull()) {
                $returnType = '?' . $returnType;
            }
            $returnType = ': ' . $returnType;
        }

        $parameters = [];

        foreach ($method->getParameters() as $parameter) {
            $parameterType = $parameter->getType();
            $parameters[] = ($parameterType->allowsNull() ? '?' : '')
                . $parameterType->getName()
                . ' $'
                . $parameter->getName();
        }

        return implode(' ', $modifier)
            . ' function '
            . $method->getName()
            . '('
            . implode(',', $parameters)
            . ')'
            . $returnType;
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
        static::getCurrentContext()
            ->defer(
                $callback,
                ...$arguments
            );
    }

    /**
     * @return DeferContext|null
     */
    public static function getCurrentContext(): ?DeferContext
    {
        return static::$currentContext ?? new DeferContext(static::$scopeType);
    }
}