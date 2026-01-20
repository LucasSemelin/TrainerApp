<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add status column to workouts
        Schema::table('workouts', function (Blueprint $table) {
            $table->string('status', 20)->default('draft')->after('client_id');
            $table->index(['client_id', 'trainer_id', 'status']);
        });

        // Convert is_current to status (only if column exists)
        if (Schema::hasColumn('workouts', 'is_current')) {
            DB::table('workouts')->where('is_current', true)->update(['status' => 'active']);
            DB::table('workouts')->where('is_current', false)->update(['status' => 'archived']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workouts', function (Blueprint $table) {
            $table->dropIndex(['client_id', 'trainer_id', 'status']);
            $table->dropColumn('status');
        });
    }
};
