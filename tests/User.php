<?php declare(strict_types=1);

namespace Junges\TrackableJobs\Tests;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'email'];

    protected $table = 'test_users';
}
