<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $indexes = DB::select("SHOW INDEX FROM attendances WHERE Key_name = 'unique_attendance'");

        if (count($indexes) === 0) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->unique(['user_id', 'lesson_schedule_id', 'date'], 'unique_attendance');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('unique_attendance');
        });
    }
};
