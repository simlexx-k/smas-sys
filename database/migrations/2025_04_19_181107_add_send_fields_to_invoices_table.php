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
        Schema::table('invoices', function (Blueprint $table) {
            $table->dateTime('last_sent_at')->nullable()->after('file_path');
            $table->string('sent_to')->nullable()->after('last_sent_at');
            $table->unsignedInteger('send_attempts')->default(0)->after('sent_to');
            $table->text('send_error')->nullable()->after('send_attempts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'last_sent_at',
                'sent_to',
                'send_attempts',
                'send_error'
            ]);
        });
    }
};
