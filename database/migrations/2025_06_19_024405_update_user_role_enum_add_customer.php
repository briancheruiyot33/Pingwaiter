<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE users
            MODIFY role ENUM('admin','restaurant','cashier','cook','waiter','customer')
            NOT NULL DEFAULT 'restaurant'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE users
            MODIFY role ENUM('admin','restaurant','cashier','cook','waiter')
            NOT NULL
        ");
    }
};
