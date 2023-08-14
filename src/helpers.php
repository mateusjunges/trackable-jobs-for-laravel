<?php

use Illuminate\Foundation\Bus\PendingDispatch;

if (! function_exists('dispatchWithoutTracking')) {
    /**
     * Dispatch a trackable job without tracking it.
     *
     * @param  mixed  $job
     * @param ...$arguments
     * @throws \ReflectionException
     * @return \Illuminate\Foundation\Bus\PendingDispatch
     */
    function dispatchWithoutTracking(mixed $job, ...$arguments): PendingDispatch
    {
        $parameters = (new ReflectionClass($job))->getConstructor()->getParameters();

        if (count($parameters) === 1 && ! count($arguments)) {
            $arguments = [false];
        }

        if (count($parameters) > 1 && count($arguments) === 1) {
            $arguments = [...$arguments, false];
        }

        if (is_string($job)) {
            $job = new $job(...$arguments);
        }

        if (! is_object($job)) {
            throw new InvalidArgumentException("Invalid [$job].");
        }

        return new PendingDispatch($job);
    }
}
