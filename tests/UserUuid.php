<?php

namespace Junges\TrackableJobs\Tests;

use Illuminate\Database\Eloquent\Model;
use Junges\TrackableJobs\Concerns\HasUuid;

class UserUuid extends Model
{
    use HasUuid;
    protected $fillable = ['name', 'email'];

    public $timestamps = false;
    protected $table = 'test_users_uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $primaryKey = 'uuid';
}
