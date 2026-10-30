<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('draft_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('template_id')->constrained('builder_templates');
            $table->string('title');
            $table->string('slug');
            $table->string('status')->default('draft');
            $table->boolean('is_primary')->default(false);
            $table->json('content');
            $table->json('published_content')->nullable();
            $table->unsignedInteger('revision')->default(1);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['store_id', 'slug']);
            $table->index(['store_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('pages'); }
};
