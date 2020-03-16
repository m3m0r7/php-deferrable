<?php declare(strict_types=1);

namespace PHPDeferrable;

function deferrable($targetClass, ...$arguments)
{
    return Deferrable::makeContextManipulator(
        $targetClass,
        ...$arguments
    );
}

/**
 * Register a callback for deferring.
 *
 * @param callable $callback
 * @param mixed ...$arguments
 */
function defer(callable $callback, &...$arguments): void
{
    Deferrable::defer(
        $callback,
        ...$arguments
    );
}
