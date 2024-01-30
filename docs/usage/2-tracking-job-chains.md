---
title: Tracking job chains
weight: 2
---

Laravel supports job chaining out of the box:

```php
Bus::dispatchChain([
    new OptimizePodcast($podcast),
    new CompressPodcast($podcast),
    new ReleasePodcast($podcast)
])->dispatch();
```


It's a nice, fluent way of saying "Run this jobs sequentially, one after the previous one is complete.".

If you have a task which takes some steps to be completed, you can track the job chain used to do that and know the
status for each job.

If you are releasing a new podcast, for example, and it has to be optimized, compressed and released, you can track
this steps by following two steps:

1 - Add a `steps` relationship to your `Podcast` model:

```php
public function steps()
{
    return $this->morphMany(Junges\TrackableJobs\Models\TrackedJob::class, 'trackable');
}
```

2 - Override the `trackableType` and `trackableKey` methods provided by the `Trackable` trait, so this package will track the jobs
and automatically relate it to the Podcast you are working on:

```php
public function trackableKey(): ?string
{
    return (string) $this->podcast->id;
}

public function trackableType(): ?string
{
    return $this->podcast->gerMorphClass();
}
```

This will store the `id` and type of your podcast in the `tracked_jobs` table. Then, it will be used to retrieve all tracked jobs that were
related to that podcast: 

```php
$steps = Podcast::find($id)->steps()->get();
```