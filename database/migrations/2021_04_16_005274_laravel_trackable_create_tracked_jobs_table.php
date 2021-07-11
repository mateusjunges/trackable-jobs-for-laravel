<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class LaravelTrackableCreateTrackedJobsTable
 */
class LaravelTrackableCreateTrackedJobsTable extends Migration
{
    /**
     * @var string
     */
    private string $table_name;

    /**
     * @var bool
     */
    private bool $usingUuid;

    /**
     * LaravelTrackableCreateTrackedJobsTable constructor.
     */
    public function __construct()
    {
        $this->table_name = config('trackable-jobs.tables.tracked_jobs', 'tracked_jobs');
        $this->usingUuid = config('trackable-jobs.using_uuid', false);
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create($this->table_name, function (Blueprint $table) {
            $this->usingUuid
                ? $table->uuid('uuid')->primary()
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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists($this->table_name);
    }
}
