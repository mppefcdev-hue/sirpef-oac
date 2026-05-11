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
        // This migration previously added memorandum_id and monto.
        // These fields are being removed in a subsequent migration.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
