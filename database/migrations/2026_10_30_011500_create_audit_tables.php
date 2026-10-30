<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('meta')->nullable();
            $table->string('ip')->nullable();
            $table->timestamps();
            $table->index(['action', 'created_at']);
        });

        Schema::create('impersonations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users');
            $table->foreignId('user_id')->constrained('users');
            $table->string('reason');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->json('actions')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('impersonations'); Schema::dropIfExists('audit_logs'); }
};
