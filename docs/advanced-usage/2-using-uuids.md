---
title: Using `UUIDs`
weight: 2
---

To use UUIDs with this package, the only additional configuration you need to do is change the `using_uuid` to `true`, in `config/trackable-jobs.php`.
Then it will automatically start using UUID's to store the tracked jobs and, if the model related to the tracked job
also uses UUID, it will be stored to the database in the `trackable_id` field.