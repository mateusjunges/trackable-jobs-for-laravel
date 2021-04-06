# Trackable Jobs For Laravel
This package allows you to track your laravel jobs!
Using this package, you can easily persist the output and the status of any job in your application.

# Installation
To install this package, use composer:
```bash
composer require mateusjunges/laravel-trackable-jobs
```

You can publish the configuration file with this command:

```bash
php artisan vendor:publish --tag=trackable-jobs-config
```

Now you are good to go!

# Usage
## Tracking jobs
To start tracking your jobs, you just need to use the `Junges\TrackableJobs\Traits\Trackable` trait in the job you want to track.
For example, let's say you want to track the status of `ProcessPodcastJob`, just add the `Trackable` trait into your job:

```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Junges\TrackableJobs\Traits\Trackable;

class ProcessPodcastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Trackable;

    public function handle()
    {
        //
    }
}
```

This trait provides 3 methods to your job: `__construct`, `failed` and `middleware`.
If you want to override any of the methods, you must copy and paste (because you can't use `parent` for traits) the content of each one inside your class,
so this package still work as intended.

This package will store the last status of your job, which can be `queued`, `started`, `failed` or `finished`. Also, it stores the 
`started_at` and `finished_at` timestamps for each tracked job.

To use it, you just need to pass any model to your Job constructor:

```php
dispatch(new ProcessPodcastJob($podcast));
```

## Tracking job chains
Laravel supports job chaining out of the box:

```php
Bus::dispatchChain([
    new OptimizePodcast($podcast),
    new ReleasePodcast($podcast)
])->dispatch();
```

It's a nice, fluent way of saying "Run this jobs sequentially, one after the previous one is complete.".

You can use this package to track the status for each step when releasing a new podcast. It can be done by adding a `steps` relationship
in your `Podcast` model:

```php
public function steps()
{
    return $this->morphMany(Junges\TrackableJobs\Models\TrackedJob::class, 'trackable');
}
```

Now, you can have the status of each job that should be processed to release your podcast:

```php
$steps = Podcast::find($id)->steps()->get();
```

# Tests
Run `composer test` to test this package.

# Contributing
Thank you for consider contributing for the Laravel Trackable Jobs package! The contribution guide can
be found [here][contributing].

# Changelog
Please see the [changelog][changelog] for more information about the changes on this package.

# Credits
- [All contributors][contributors]

# License
The laravel trackable jobs is open-sourced software licensed under the terms of [MIT License][mit]. Please see the [license file][license] for more information.

[contributing]: CONTRIBUTING.md
[changelog]: CHANGELOG.md
[mit]: https://opensource.org/licenses/MIT
[license]: LICENSE
[contributors]: https://github.com/mateusjunges/trackable-jobs-for-laravel/graphs/contributors