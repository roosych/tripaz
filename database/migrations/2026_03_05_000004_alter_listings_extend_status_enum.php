<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL only — SQLite does not support MODIFY COLUMN
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE listings MODIFY COLUMN status ENUM('draft','pending_review','published','awaiting_payment','rejected','suspended') NOT NULL DEFAULT 'draft'");
        }
    }

    public function down(): void
    {
        // MySQL only — SQLite does not support MODIFY COLUMN
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("UPDATE listings SET status = 'draft' WHERE status = 'awaiting_payment'");
            DB::statement("ALTER TABLE listings MODIFY COLUMN status ENUM('draft','pending_review','published','rejected','suspended') NOT NULL DEFAULT 'draft'");
        }
    }
};
