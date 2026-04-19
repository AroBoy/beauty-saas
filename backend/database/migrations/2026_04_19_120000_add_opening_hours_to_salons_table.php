<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salons', function (Blueprint $table) {
            $table->time('opening_time')->default('09:00:00')->after('sms_reminder_hours');
            $table->time('closing_time')->default('17:00:00')->after('opening_time');
        });

        DB::table('salons')
            ->whereNull('opening_time')
            ->update(['opening_time' => '09:00:00']);

        DB::table('salons')
            ->whereNull('closing_time')
            ->update(['closing_time' => '17:00:00']);
    }

    public function down(): void
    {
        Schema::table('salons', function (Blueprint $table) {
            $table->dropColumn(['opening_time', 'closing_time']);
        });
    }
};
