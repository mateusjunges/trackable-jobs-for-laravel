<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tables
    |--------------------------------------------------------------------------
    |
    | The table where the tracked jobs will be stored.
    | By default, it's called 'tracked_jobs'.
    |
    */

    'tables' => [
        'tracked_jobs' => 'tracked_jobs',
    ],

    /*
    |--------------------------------------------------------------------------
    | Using uuid
    |--------------------------------------------------------------------------
    |
    | When true trackable will use uuid.
    |
    */

    'using_uuid' => false,

];
