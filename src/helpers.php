<?php

use Illuminate\Foundation\Bus\PendingDispatch;

if (! function_exists('dispatchWithoutTracking'))
{
    /**
     * @param  mixed  $job
     * @param ...$args
     * @return \Illuminate\Foundation\Bus\PendingDispatch
     */
    function dispatchWithoutTracking($job, ...$args): PendingDispatch
    {
        $arguments = [...$args, false];

        if (is_string($job)) {
            $job = new $job(...$arguments);
        }

        if (! is_object($job)) {
            throw new InvalidArgumentException("Invalid [$job].");
        }

        return new PendingDispatch($job);
    }
}