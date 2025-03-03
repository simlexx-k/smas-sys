<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('school_type')->nullable();
            $table->string('status')->default('active');
            $table->string('subscription_plan')->default('basic');
            $table->timestamp('subscription_ends_at')->nullable();
            $table->json('settings')->nullable();
        });
    }

    public function down()
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'address', 'phone', 'email', 'logo_url', 'school_type',
                'status', 'subscription_plan', 'subscription_ends_at', 'settings'
            ]);
        });
    }
}; 