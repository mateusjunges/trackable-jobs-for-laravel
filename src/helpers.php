<?php

use Illuminate\Foundation\Bus\PendingDispatch;

if (! function_exists('dispatchWithoutTracking')) {
    /**
     * Dispatch a trackable job without tracking it.
     *
     * @param  string|object  $job
     * @param ...$arguments
     * @return \Illuminate\Foundation\Bus\PendingDispatch
     */
    function dispatchWithoutTracking(string | object $job, ...$arguments): PendingDispatch
    {
        if (is_string($job) && class_exists($job)) {
            $job::$shouldBeTracked = false;

            $jobInstance = new $job(...$arguments);

            $job::$shouldBeTracked = true;

            return new PendingDispatch($jobInstance);
        }

        if (is_object($job)) {
            $class = get_class($job);
            assert(class_exists($class));

            $class::$shouldBeTracked = false;

            $jobInstance = new $class(...$arguments);

            $class::$shouldBeTracked = true;

            return new PendingDispatch($jobInstance);
        }

        throw new InvalidArgumentException("Invalid [$job].");
    }
}
