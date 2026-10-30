<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('custom_domain')->nullable()->unique();
            $table->string('custom_domain_status', 32)->nullable();
            $table->timestamp('custom_domain_verified_at')->nullable();
            $table->timestamp('custom_domain_checked_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn([
                'custom_domain',
                'custom_domain_status',
                'custom_domain_verified_at',
                'custom_domain_checked_at',
            ]);
        });
    }
};
