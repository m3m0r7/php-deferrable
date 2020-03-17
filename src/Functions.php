<?php declare(strict_types=1);

namespace PHPDeferrable;

if (!function_exists('deferrable')) {
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
        return Deferrable::makeContextManipulator(
            $targetClass,
            ...$arguments
        );
    }
}

if (!function_exists('defer')) {
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
}
