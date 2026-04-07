<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendance_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('attendance_settings', 'latitude')) {
                $table->double('latitude')->nullable()->after('id');
            }
            if (!Schema::hasColumn('attendance_settings', 'longitude')) {
                $table->double('latitude')->nullable()->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_settings', function (Blueprint $table) {
            if (Schema::hasColumn('attendance_settings', 'latitude')) {
                $table->dropColumn('latitude');
            }
            if (Schema::hasColumn('attendance_settings', 'longitude')) {
                $table->dropColumn('longitude');
            }
        });
    }
};
