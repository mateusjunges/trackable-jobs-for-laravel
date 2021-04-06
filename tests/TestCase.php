<?php

namespace Junges\TrackableJobs\Tests;

use Illuminate\Database\Schema\Blueprint;
use Junges\TrackableJobs\Providers\TrackableJobsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->configureDatabase($this->app);

        $this->user = User::first();

        (new TrackableJobsServiceProvider($this->app))->boot();
    }

    public function getPackageProviders($app)
    {
        return [
            TrackableJobsServiceProvider::class
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    private function configureDatabase($app)
    {
        $app['config']->set('trackable-jobs.tables.tracked_jobs', 'tracked_jobs');

        $app['db']->connection()->getSchemaBuilder()->create('test_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email');
        });

        include_once __DIR__.'/../database/migrations/2021_04_16_005274_laravel_trackable_create_tracked_jobs_table.php';

        (new \LaravelTrackableCreateTrackedJobsTable())->up();

        User::create([
            'name' => 'Test user',
            'email' => 'test@test.com'
        ]);
    }
}