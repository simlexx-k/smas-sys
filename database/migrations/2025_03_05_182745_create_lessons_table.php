<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])
                  ->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Add indexes for better query performance
            $table->index(['tenant_id', 'teacher_id', 'start_time']);
            $table->index(['class_id', 'start_time']);
            $table->index(['subject_id', 'start_time']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('lessons');
    }
};