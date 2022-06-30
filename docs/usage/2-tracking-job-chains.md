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
this steps by adding a `steps` relationship to your `Podcast` model:

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