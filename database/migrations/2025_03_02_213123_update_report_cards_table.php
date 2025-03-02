<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateReportCardsTable extends Migration
{
    public function up()
    {
        Schema::table('report_cards', function (Blueprint $table) {
            if (!Schema::hasColumn('report_cards', 'student_id')) {
                $table->foreignId('student_id')->constrained('students');
            }
            if (!Schema::hasColumn('report_cards', 'exam_id')) {
                $table->foreignId('exam_id')->constrained('exams');
            }
            if (!Schema::hasColumn('report_cards', 'subject_id')) {
                $table->foreignId('subject_id')->constrained('subjects');
            }
            if (!Schema::hasColumn('report_cards', 'score')) {
                $table->decimal('score', 5, 2);
            }
            if (!Schema::hasColumn('report_cards', 'remarks')) {
                $table->string('remarks')->nullable();
            }
            if (!Schema::hasColumn('report_cards', 'grade')) {
                $table->string('grade');
            }
        });
    }

    public function down()
    {
        Schema::table('report_cards', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropForeign(['exam_id']);
            $table->dropForeign(['subject_id']);
            $table->dropColumn(['student_id', 'exam_id', 'subject_id', 'score', 'remarks', 'grade']);
        });
    }
}
