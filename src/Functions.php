<?php declare(strict_types=1);

namespace PHPDeferrable;

define('DEFER_ANONYMOUS_CLASS_PREFIX', '__defer__anonymous_');
define('DEFER_GLOBAL_NAME', '__temp_defers__');
define('DEFER_ANONYMOUS_SCOPE_NAME', 'anonymous');

$GLOBALS[DEFER_GLOBAL_NAME] = [
    'current' => null,
    'temporary_classes_count' => 0,
    'definitions' => null,
];

$GLOBALS[DEFER_GLOBAL_NAME][DEFER_ANONYMOUS_SCOPE_NAME] = createDeferContext();

/**
 * Create a defer context.
 *
 * @param string|null $className
 * @param string|null $methodName
 * @return DeferContext
 */
function createDeferContext(?string $className = null, ?string $methodName = null): DeferContext
{
    /**
     * @var string $currentStackName
     */
    $currentStackName = $GLOBALS[DEFER_GLOBAL_NAME]['current'];
    if (!$currentStackName) {
        return $GLOBALS[DEFER_GLOBAL_NAME][DEFER_ANONYMOUS_SCOPE_NAME] ?? new DeferContext();
    }
    return $GLOBALS[DEFER_GLOBAL_NAME]['definitions'][$currentStackName] = new DeferContext();
}

/**
 * Consume deferred stacks.
 *
 * @param DeferContext $context
 * @param string|null $className
 * @param string|null $methodName
 */
function consumeDefers(DeferContext $context, ?string $className = null, ?string $methodName = null): void
{
    /**
     * @var string $currentStackName
     */
    $currentStackName = $GLOBALS[DEFER_GLOBAL_NAME]['current'];
    $definition = $GLOBALS[DEFER_GLOBAL_NAME]['definitions'];

    /**
     * @var DeferContext $context
     */
    $context = $definition[$currentStackName] ?? $GLOBALS[DEFER_GLOBAL_NAME]['anonymous'];
    $context->consume();
}

/**
 * @param callable $deferrableFunction
 * @param mixed ...$arguments pass parameters into a function
 * @return mixed
 */
function deferrableFunction(callable $deferrableFunction, ...$arguments)
{
    try {
        $GLOBALS[DEFER_GLOBAL_NAME]['current'] = 'anonymous@function';
        $context = createDeferContext(null, null);
        $result = $deferrableFunction(...$arguments);
    } finally {
        consumeDefers($context, null, null);
        $GLOBALS[DEFER_GLOBAL_NAME]['current'] = null;
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
function deferrable($targetClass, ...$arguments)
{
    if (is_callable($targetClass)) {
        return deferrableFunction($targetClass, ...$arguments);
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
            . '$GLOBALS[\'' . DEFER_GLOBAL_NAME . '\'][\'current\'] = __CLASS__ . \'::\' . __METHOD__;'
            . '$deferContext = \\' . __NAMESPACE__ . '\\createDeferContext(__CLASS__, __METHOD__); '
            . '$result = parent::' . $methodName . '(...func_get_args()); '
            . '} finally {'
            . '\\' . __NAMESPACE__ . '\\consumeDefers($deferContext, __CLASS__, __METHOD__);'
            . '$GLOBALS[\'' . DEFER_GLOBAL_NAME . '\'][\'current\'] = null;'
            . '}'
            . 'return $result; '
            . '}';
    }

    $temporaryClassName = DEFER_ANONYMOUS_CLASS_PREFIX . ($GLOBALS[DEFER_GLOBAL_NAME]['temporary_classes_count']++);
    eval('class ' . $temporaryClassName . ' extends ' . $targetClass . ' implements \\' . __NAMESPACE__ . '\\DeferrableInterface { ' . implode($body) . ' }');
    return new $temporaryClassName(...$arguments);
}

/**
 * Register a callback for deferring.
 *
 * @param callable $callback
 * @param mixed ...$arguments
 */
function defer(callable $callback, &...$arguments): void
{
    /**
     * @var string $currentStackName
     */
    $currentStackName = $GLOBALS[DEFER_GLOBAL_NAME]['current'];

    if (!$currentStackName) {
        $GLOBALS[DEFER_GLOBAL_NAME][DEFER_ANONYMOUS_SCOPE_NAME]->defer(
            $callback,
            ...$arguments
        );
        return;
    }
    $GLOBALS[DEFER_GLOBAL_NAME]['definitions'][$currentStackName]
        ->defer(
            $callback,
            ...$arguments
        );
}
