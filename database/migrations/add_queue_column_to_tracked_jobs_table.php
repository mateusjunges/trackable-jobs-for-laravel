<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $table_name;

    public function __construct()
    {
        $this->table_name = config('trackable-jobs.tables.tracked_jobs', 'tracked_jobs');
    }

    public function up(): void
    {
        Schema::table($this->table_name, function (Blueprint $table) {
            $table->string('queue')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table($this->table_name, function (Blueprint $table) {
            $table->dropColumn('queue');
        });
    }
};