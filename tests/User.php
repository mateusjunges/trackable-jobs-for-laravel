<?php

namespace Junges\TrackableJobs\Tests;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Model
{
    protected $fillable = ['name', 'email'];
    public $timestamps = false;
    protected $table = 'test_users';
}
