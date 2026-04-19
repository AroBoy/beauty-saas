<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salons', function (Blueprint $table) {
            $table->time('saturday_opening_time')->nullable()->after('closing_time');
            $table->time('saturday_closing_time')->nullable()->after('saturday_opening_time');
            $table->time('sunday_opening_time')->nullable()->after('saturday_closing_time');
            $table->time('sunday_closing_time')->nullable()->after('sunday_opening_time');
        });
    }

    public function down(): void
    {
        Schema::table('salons', function (Blueprint $table) {
            $table->dropColumn([
                'saturday_opening_time',
                'saturday_closing_time',
                'sunday_opening_time',
                'sunday_closing_time',
            ]);
        });
    }
};
