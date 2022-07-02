<?php

use Illuminate\Foundation\Bus\PendingDispatch;

if (! function_exists('dispatchWithoutTracking')) {
    /**
     * Dispatch a trackable job without tracking it.
     *
     * @param  mixed  $job
     * @param ...$arguments
     * @return \Illuminate\Foundation\Bus\PendingDispatch
     */
    function dispatchWithoutTracking($job, ...$arguments): PendingDispatch
    {
        if (! count($arguments)) {
            $arguments = [null, false];
        } else {
            $arguments = [
                ...$arguments,
                false,
            ];
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
