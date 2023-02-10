<?php

namespace Junges\TrackableJobs\Tests;

use Illuminate\Database\Schema\Blueprint;
use Junges\TrackableJobs\Providers\TrackableJobsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public $user;
    public $userUuid;

    public function setUp(): void
    {
        parent::setUp();

        $this->configureDatabase($this->app);

        $this->user = User::first();
        $this->userUuid = UserUuid::first();

        (new TrackableJobsServiceProvider($this->app))->boot();
    }

    public function getPackageProviders($app): array
    {
        return [
            TrackableJobsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('trackable-jobs.using_uuid', true);
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    private function configureDatabase($app)
    {
        $app['config']->set('trackable-jobs.tables.tracked_jobs', 'tracked_jobs');
        $app['config']->set('queue.default', 'database');

        $app['db']->connection()->getSchemaBuilder()->create('test_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email');
        });

        $app['db']->connection()->getSchemaBuilder()->create('test_users_uuid', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('name');
            $table->string('email');
        });

        $app['db']->connection()->getSchemaBuilder()->create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });
        
        
        $app['db']->connection()->getSchemaBuilder()->create('tracked_jobs', function (Blueprint $table) use ($app) {
            $app['config']->get('trackable-jobs.using_uuid')
                ? $table->uuid()
                : $table->id();
            $table->string('trackable_id')->index();
            $table->string('trackable_type')->index();
            $table->string('name');
            $table->string('status')->default('queued');
            $table->longText('output')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        User::create([
            'name' => 'Test user',
            'email' => 'test@test.com',
        ]);

        UserUuid::create([
            'name' => 'Test user uuid',
            'email' => 'test@test-uuid.com',
        ]);
    }
}
