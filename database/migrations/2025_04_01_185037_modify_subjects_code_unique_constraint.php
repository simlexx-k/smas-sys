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
        Schema::table('subjects', function (Blueprint $table) {
            // Drop the existing unique constraint
            $table->dropUnique('subjects_code_unique');
            
            // Add a new composite unique constraint
            $table->unique(['code', 'tenant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            // Remove the composite unique constraint
            $table->dropUnique(['code', 'tenant_id']);
            
            // Restore the original unique constraint
            $table->unique('code');
        });
    }
};
