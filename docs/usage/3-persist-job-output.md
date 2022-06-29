---
title: Persist job output
weight: 3
---

To persist the output of your job to the database, you only need to return something from your job. By default, if your job throws an exception, the output stored in the database will be the message of the given exception. If your job finishes successfully, you don't have to return anything, but you can store it's output by just returning something after the job is done. For example:

```php
public function handle()
{
    //Do your stuff here
    
    return "Job finished successfully";
}
```
The string `Job finished successfully` will be stored as the output of this job.