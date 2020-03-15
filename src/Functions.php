<?php declare(strict_types=1);
namespace PHPDeferrable;

define('DEFER_GLOBAL_NAME', '__temp_defers__');

$GLOBALS[DEFER_GLOBAL_NAME] = [
    'current' => null,
    'temporary_classes_count' => 0,
    'definitions' => null,
];

$GLOBALS[DEFER_GLOBAL_NAME]['anonymous'] = createDeferContext();

function createDeferContext(?string $className = null, ?string $methodName = null): DeferContext
{
    /**
     * @var string $currentStackName
     */
    $currentStackName = $GLOBALS[DEFER_GLOBAL_NAME]['current'];
    return $GLOBALS[DEFER_GLOBAL_NAME]['definitions'][$currentStackName] = new DeferContext();
}

function consumeDefers(DeferContext $context, ?string $className = null, ?string $methodName = null)
{
    /**
     * @var string $currentStackName
     */
    $currentStackName = $GLOBALS[DEFER_GLOBAL_NAME]['current'];
    $definition = $GLOBALS[DEFER_GLOBAL_NAME]['definitions'];
    try {
        while ($callback = ($definition[$currentStackName] ?? $GLOBALS[DEFER_GLOBAL_NAME]['anonymous'])->pop()) {
            $callback();
        }
    } catch (\RuntimeException $e) {

    }
    unset($context);
}

function deferrableFunction(callable $deferrableFunction)
{
    try {
        $GLOBALS[DEFER_GLOBAL_NAME]['current'] = 'anonymous@function';
        $context = createDeferContext(null, null);
        $deferrableFunction();
    } finally {
        consumeDefers($context, null, null);
        $GLOBALS[DEFER_GLOBAL_NAME]['current'] = null;
    }
    return null;
}

function deferrable($targetClass, ...$arguments)
{
    if (is_callable($targetClass)) {
        return deferrableFunction($targetClass);
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

        return implode(' ', $modifier);
    };

    foreach ($reflection->getMethods() as $method) {
        $methodName = $method->getName();
        if ($method->isAbstract()) {
            continue;
        }
        $body[] = $makeModifier($method) . ' function ' . $methodName . '() { try{ '
            . '$GLOBALS[\'' . DEFER_GLOBAL_NAME . '\'][\'current\'] = __CLASS__ . \'::\' . __METHOD__;'
            . '$deferContext = \\' . __NAMESPACE__ . '\\createDeferContext(__CLASS__, __METHOD__); ' 
            . '$result = parent::' . $methodName . '(...func_get_args()); '
            . '\\' . __NAMESPACE__ . '\\consumeDefers($deferContext, __CLASS__, __METHOD__);'
            . '} finally { $GLOBALS[\'' . DEFER_GLOBAL_NAME . '\'][\'current\'] = null; }'
            . 'return $result; '
            . '}';
    }

    $temporaryClassName = '__defer__anonymous_' . ($GLOBALS['__temp_defers__']['temporary_classes_count']++);
    eval('class ' . $temporaryClassName . ' extends ' . $targetClass . ' implements \\' . __NAMESPACE__ . '\\DeferrableInterface { ' . implode($body) . ' };');
    return new $temporaryClassName(...$arguments);
}

function defer(callable $callback)
{
    /**
     * @var string $currentStackName
     */
    $currentStackName = $GLOBALS[DEFER_GLOBAL_NAME]['current'];

    if (!$currentStackName) {
        $GLOBALS[DEFER_GLOBAL_NAME]['anonymous']->defer(
            $callback
        );
        return;
    }
    $GLOBALS[DEFER_GLOBAL_NAME]['definitions'][$currentStackName]
        ->defer($callback);
}
