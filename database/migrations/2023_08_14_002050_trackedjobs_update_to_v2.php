<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function __construct(
        private string $table_name = '',
    )
    {
        $this->table_name = config('trackable-jobs.tables.tracked_jobs', 'tracked_jobs');
    }

    public function up(): void
    {
        Schema::table($this->table_name, function (Blueprint $table) {
            $table->string('trackable_id')->nullable()->change();
            $table->string('trackable_type')->nullable()->change();
            $table->string('job_id')->nullable()->after('name');
            $table->integer('attempts')->default(1)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table($this->table_name, function (Blueprint $table) {
            $table->string('trackable_id')->nullable(false)->change();
            $table->string('trackable_type')->nullable(false)->change();
            $table->dropColumn('job_id');
            $table->dropColumn('attempts');
        });
    }
};
