<?php declare(strict_types=1);

namespace Junges\TrackableJobs\Tests;

use Illuminate\Database\Eloquent\Model;
use Junges\TrackableJobs\Concerns\HasUuid;

class UserUuid extends Model
{
    use HasUuid;

    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = ['name', 'email'];

    protected $table = 'test_users_uuid';

    protected $keyType = 'string';

    protected $primaryKey = 'uuid';
}
