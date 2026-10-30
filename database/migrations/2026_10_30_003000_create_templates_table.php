<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('brand');
            $table->string('headline');
            $table->string('subline')->nullable();
            $table->string('product_name')->nullable();
            $table->string('price')->nullable();
            $table->string('old_price')->nullable();
            $table->string('badge')->nullable();
            $table->string('art')->default('gift');
            $table->json('palette')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
