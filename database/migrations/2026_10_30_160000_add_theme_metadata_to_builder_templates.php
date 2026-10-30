<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('builder_templates', function (Blueprint $table) {
            $table->string('theme_version', 20)->default('1.0.0')->after('design');
            $table->string('preview')->nullable()->after('thumbnail');
            $table->json('suitable_for')->nullable()->after('tagline');
        });
    }

    public function down(): void
    {
        Schema::table('builder_templates', function (Blueprint $table) {
            $table->dropColumn(['theme_version', 'preview', 'suitable_for']);
        });
    }
};
