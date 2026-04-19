<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salons', function (Blueprint $table) {
            $table->boolean('saturday_closed')->default(false)->after('saturday_closing_time');
            $table->boolean('sunday_closed')->default(false)->after('saturday_closed');
        });
    }

    public function down(): void
    {
        Schema::table('salons', function (Blueprint $table) {
            $table->dropColumn(['saturday_closed', 'sunday_closed']);
        });
    }
};
