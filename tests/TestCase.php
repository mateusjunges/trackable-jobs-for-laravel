<?php

declare(strict_types=1);

namespace Junges\TrackableJobs\Tests;

use Illuminate\Database\Schema\Blueprint;
use Junges\TrackableJobs\Providers\TrackableJobsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

/**
 * Class TestCase
 * @package Junges\TrackableJobs\Tests
 */
class TestCase extends Orchestra
{
    public $user;
    public $userUuid;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->configureDatabase($this->app);

        $this->user = User::first();
        $this->userUuid = UserUuid::first();

        (new TrackableJobsServiceProvider($this->app))->boot();
    }

    /**
     * @param $app
     *
     * @return array
     */
    public function getPackageProviders($app): array
    {
        return [
            TrackableJobsServiceProvider::class,
        ];
    }

    /**
     * @param $app
     *
     * @return void
     */
    public function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('trackable-jobs.using_uuid', true);
        $app['config']->set('database.connections.sqlite', [
            'driver'    => 'sqlite',
            'database'  => ':memory:',
            'prefix'    => '',
        ]);
    }

    /**
     * @param $app
     *
     * @return void
     */
    private function configureDatabase($app): void
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

        User::create([
            'name'  => 'Test user',
            'email' => 'test@test.com',
        ]);

        UserUuid::create([
            'name'  => 'Test user uuid',
            'email' => 'test@test-uuid.com',
        ]);
    }
}
