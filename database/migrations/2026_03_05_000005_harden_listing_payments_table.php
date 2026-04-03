<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listing_payments', function (Blueprint $table) {
            // Drop existing cascade FK constraints and replace with restrict
            $table->dropForeign(['listing_id']);
            $table->dropForeign(['user_id']);

            $table->foreign('listing_id')
                ->references('id')->on('listings')
                ->restrictOnDelete();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->restrictOnDelete();

            // Add payment_type enum (publish is the only type for now)
            $table->enum('payment_type', ['publish'])->default('publish')->after('currency');

            // Add cancelled_at for host withdrawals
            $table->timestamp('cancelled_at')->nullable()->after('reviewed_at');
        });

        // Extend the status enum to include 'cancelled'
        // MySQL requires a full column redefinition to add enum values (SQLite skipped)
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE listing_payments MODIFY COLUMN status ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        Schema::table('listing_payments', function (Blueprint $table) {
            $table->dropForeign(['listing_id']);
            $table->dropForeign(['user_id']);

            $table->foreign('listing_id')
                ->references('id')->on('listings')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->cascadeOnDelete();

            $table->dropColumn(['payment_type', 'cancelled_at']);
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE listing_payments MODIFY COLUMN status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending'");
        }
    }
};
