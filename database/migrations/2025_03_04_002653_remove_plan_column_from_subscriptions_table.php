<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('subscriptions', 'plan')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->dropColumn('plan');
            });
        }
    }

    public function down()
    {
        if (!Schema::hasColumn('subscriptions', 'plan')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->string('plan')->nullable();
            });
        }
    }
};