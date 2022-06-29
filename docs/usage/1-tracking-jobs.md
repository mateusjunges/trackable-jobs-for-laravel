---
title: Tracking Jobs
weight: 1
---

To start tracking your jobs, you just need to use the `Junges\TrackableJobs\Concerns\Trackable` trait in the job you want to track. For example, let's say you want to track the status of `ProcessPodcastJob`, just add the Trackable trait into your job:


```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Junges\TrackableJobs\Concerns\Trackable;

class ProcessPodcastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Trackable;

    public function handle()
    {
        //
    }
}
```


This trait provides 3 methods to your job: `__construct`, `failed` and `middleware`. It also adds a `model` public property to the job class.
If you want to override any of the methods, you must copy and paste (because you can't use `parent` for traits) the content of each one inside your class,
so this package still work as intended.

For example: if you need to change the constructor of your job, you can use the `Junges\TrackableJobs\Concerns\Trackable` and alias the `__construct` with some other name, for example:


```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Junges\TrackableJobs\Concerns\Trackable;
use App\Models\Podcast;
use Junges\TrackableJobs\Models\TrackedJob;

class ProcessPodcastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Trackable {
        __construct as __baseConstruct;
    }

    public function __construct(Podcast $podcast)
    {
         $this->__baseConstruct($podcast);
         
         // Add your code here.
    }

    public function handle()
    {
        //
    }
}
```

It can be done with any method you want to change.

This package will store the last status of your job, which can be `queued`, `started`, `failed` or `finished`. Also, it stores the
`started_at` and `finished_at` timestamps for each tracked job.

To use it, you just need to pass any model to your Job constructor:

```php
dispatch(new ProcessPodcastJob($podcast));
```

Once this trait is added to your job, your job progress will be persisted to the database. You can configure the table name by publishing this package configuration file:

```shell
php artisan vendor:publish --tag=trackable-jobs-config
```

This command will create a new config file in `config/trackable-jobs.php`, with this content:

```php
<?php

return [
    /*
     | The table where the tracked jobs will be stored.
     | By default, it's called 'tracked_jobs'.
     */
    'tables' => [
        'tracked_jobs' => 'tracked_jobs',
    ],
    'using_uuid' => false,
];
```
