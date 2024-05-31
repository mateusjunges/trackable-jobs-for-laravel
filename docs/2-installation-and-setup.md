---
title: Installation and setup
weight: 2
---

To install this package, you must be running php v8.2 or higher, as well as laravel v10.x or higher.

Use composer:
```bash
composer require mateusjunges/laravel-trackable-jobs
```

You can publish the configuration file with this command:

```bash
php artisan vendor:publish --tag=trackable-jobs-assets
```

Run `php artisan migrate` to migrate the table needed by this package, and now you are good to go!
