<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Drop the old plan column if it exists
            if (Schema::hasColumn('subscriptions', 'plan')) {
                $table->dropColumn('plan');
            }

            // Add plan_id if it doesn't exist
            if (!Schema::hasColumn('subscriptions', 'plan_id')) {
                $table->foreignId('plan_id')->nullable()->constrained('plans')->restrictOnDelete();
            }
        });
    }

    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('plan')->nullable();
            $table->dropForeign(['plan_id']);
            $table->dropColumn('plan_id');
        });
    }
}; 