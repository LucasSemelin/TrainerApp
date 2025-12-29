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
        Schema::table('client_trainer', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive', 'pending'])
                ->default('pending')
                ->after('trainer_id');
            $table->string('invitation_token', 60)->nullable()->after('status');
            $table->timestamp('invited_at')->nullable()->after('invitation_token');
            $table->timestamp('accepted_at')->nullable()->after('invited_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_trainer', function (Blueprint $table) {
            $table->dropColumn(['status', 'invitation_token', 'invited_at', 'accepted_at']);
        });
    }
};
