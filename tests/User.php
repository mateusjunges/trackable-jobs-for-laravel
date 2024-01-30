<?php

namespace Junges\TrackableJobs\Tests;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = ['name', 'email'];
    public $timestamps = false;
    protected $table = 'test_users';
}
