<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('class_teacher', function (Blueprint $table) {
            // First check if the old column exists and new one doesn't
            if (Schema::hasColumn('class_teacher', 'class_id') && !Schema::hasColumn('class_teacher', 'school_class_id')) {
                // Rename the column
                $table->renameColumn('class_id', 'school_class_id');
            }
            // If neither column exists, create the correct one
            else if (!Schema::hasColumn('class_teacher', 'school_class_id')) {
                $table->foreignId('school_class_id')->constrained('classes')->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::table('class_teacher', function (Blueprint $table) {
            $table->renameColumn('school_class_id', 'class_id');
        });
    }
};