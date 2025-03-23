<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Add plan_id column after id
            //$table->foreignId('plan_id')
               // ->after('id')
               // ->nullable()
              //  ->constrained()
               // ->onDelete('restrict');

            // Make plan column nullable since we're transitioning to plan_id
           // $table->string('plan')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Remove the foreign key constraint
            $table->dropForeign(['plan_id']);
            // Remove the column
            $table->dropColumn('plan_id');
            // Make plan column required again
            //$table->string('plan')->nullable(false)->change();
        });
    }
}; 
