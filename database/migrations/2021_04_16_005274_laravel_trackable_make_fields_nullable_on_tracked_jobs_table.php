<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LaravelTrackableMakeFieldsNullableOnTrackedJobsTable extends Migration
{
    private string $table_name = '';

    public function __construct()
    {
        $this->table_name = config('trackable-jobs.tables.tracked_jobs', 'tracked_jobs');
    }

    public function up()
    {
        Schema::table($this->table_name, function (Blueprint $table) {
            $table->string('trackable_id')->nullable()->change();
            $table->string('trackable_type')->nullable()->change();
        });
    }
}
