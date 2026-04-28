<?php declare(strict_types=1);

return [
    /*
     | The table where the tracked jobs will be stored.
     | By default, it's called 'tracked_jobs'.
     */
    'tables' => [
        'tracked_jobs' => 'tracked_jobs',
    ],

    /*
     | Determine weather you are using uuid or not.
     */
    'using_uuid' => false,

    /*
     | This config determine how old your (in days) models should be before they get pruned.
     | If null, it will not delete any models.
     */
    'prunable_after' => null,

    /*
     | Override this to use your own model.
     */
    'model' => Junges\TrackableJobs\Models\TrackedJob::class,

    /*
     | Optional context wrapper used for tracked-job DB operations (create/find).
     |
     | Signature:
     | fn (\Junges\TrackableJobs\TrackableJob $job, callable $callback): mixed
     |
     | Use this to bind tenant / connection context centrally instead of per-job overrides.
     */
    'tracking_context' => null,
];
