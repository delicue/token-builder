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
        Schema::table('token_presets', function (Blueprint $table) {
            $table->text('description')->nullable()->after('toughness');
            $table->json('colors')->nullable()->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('token_presets', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->dropColumn('colors');
        });
    }
};
