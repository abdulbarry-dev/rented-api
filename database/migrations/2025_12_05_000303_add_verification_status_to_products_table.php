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
        Schema::table('products', function (Blueprint $table) {
            $table->enum('verification_status', ['pending', 'approved', 'rejected'])
                ->default('pending')
                ->after('is_available');
            $table->text('rejection_reason')->nullable()->after('verification_status');
            $table->timestamp('verified_at')->nullable()->after('rejection_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['verification_status', 'rejection_reason', 'verified_at']);
        });
    }
};
