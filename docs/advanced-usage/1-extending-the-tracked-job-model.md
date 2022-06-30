---
title: Extending the `TrackedJob` model
weight: 1
---

If, for some reason, you need to use your own custom model to the TrackedJob table, you can just create a new model
and extend the existing `Junges\TrackableJobs\Models\TrackedJob::class`.
Then, you need to bind the `Junges\TrackableJobs\Contracts\TrackableJobContract` to the new model, within your `AppServiceProvider`:

```php
<?php

namespace App\Providers;

use App\Models\YourCustomModel;
use Illuminate\Support\ServiceProvider;
use Junges\TrackableJobs\Contracts\TrackableJobContract;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(TrackableJobContract::class, YourCustomModel::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
```

