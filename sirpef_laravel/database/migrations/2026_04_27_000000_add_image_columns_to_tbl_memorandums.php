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
        Schema::table('tbl_memorandums', function (Blueprint $blueprint) {
            $blueprint->text('header_img')->nullable();
            $blueprint->text('footer_img')->nullable();
            $blueprint->text('firma_img')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_memorandums', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['header_img', 'footer_img', 'firma_img']);
        });
    }
};
