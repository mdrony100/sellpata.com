<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drafts', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('builder_template_id')->constrained('builder_templates')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('anonymous_identity_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->json('content');
            $table->unsignedInteger('revision')->default(1);
            $table->string('status')->default('draft');
            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('last_saved_at')->nullable();
            $table->timestamps();

            $table->index(['anonymous_identity_id', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drafts');
    }
};
