<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lesson_schedules', function (Blueprint $table) {
            $table->dropColumn('tolerance_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('lesson_schedules', function (Blueprint $table) {
            $table->integer('tolerance_minutes')
                ->default(15);
        });
    }
};
