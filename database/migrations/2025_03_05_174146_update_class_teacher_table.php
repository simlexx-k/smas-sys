<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('class_teacher', function (Blueprint $table) {
            // Check if the column exists before renaming
            if (Schema::hasColumn('class_teacher', 'school_class_id')) {
                // Drop foreign key constraints if they exist
                $this->dropForeignKeyIfExists('class_teacher', 'school_class_id');
                
                // Rename the column
                $table->renameColumn('school_class_id', 'class_id');
            } else if (!Schema::hasColumn('class_teacher', 'class_id')) {
                // If neither column exists, create class_id
                $table->foreignId('class_id');
            }
            
            // Only add the foreign key if it doesn't exist
            if (!$this->foreignKeyExists('class_teacher', 'class_id')) {
                $table->foreign('class_id')
                    ->references('id')
                    ->on('classes')
                    ->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::table('class_teacher', function (Blueprint $table) {
            if (Schema::hasColumn('class_teacher', 'class_id')) {
                // Drop foreign key constraints if they exist
                $this->dropForeignKeyIfExists('class_teacher', 'class_id');
                
                // Rename back to original
                $table->renameColumn('class_id', 'school_class_id');
                
                // Add back the original foreign key
                $table->foreign('school_class_id')
                    ->references('id')
                    ->on('classes')
                    ->onDelete('cascade');
            }
        });
    }

    private function dropForeignKeyIfExists($table, $column)
    {
        if ($this->foreignKeyExists($table, $column)) {
            Schema::table($table, function (Blueprint $table) use ($column) {
                $table->dropForeign([$column]);
            });
        }
    }

    private function foreignKeyExists($table, $column)
    {
        $constraintName = $table . '_' . $column . '_foreign';
        
        return !empty(DB::select("
            SELECT constraint_name 
            FROM information_schema.table_constraints 
            WHERE table_name = ? 
            AND constraint_name = ?
            AND constraint_type = 'FOREIGN KEY'
        ", [$table, $constraintName]));
    }
};