<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('exams', function (Blueprint $table) {
            // Add term_id foreign key
            $table->foreignId('term_id')->after('tenant_id')->nullable()->constrained()->onDelete('cascade');
            
            // Add status column
            $table->enum('status', ['draft', 'active', 'completed'])->default('draft')->after('date');
            
            // Rename date to start_date and add end_date
            $table->renameColumn('date', 'start_date');
            $table->date('end_date')->nullable()->after('start_date');
        });
    }

    public function down()
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropForeign(['term_id']);
            $table->dropColumn('term_id');
            $table->dropColumn('status');
            $table->renameColumn('start_date', 'date');
            $table->dropColumn('end_date');
        });
    }
};