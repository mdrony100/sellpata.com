<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnUpdate();
            $table->string('username')->unique();
            $table->string('username_lower')->unique();
            $table->string('display_name');
            $table->string('logo_path')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->boolean('delivery_charge_enabled')->default(true);
            $table->unsignedInteger('delivery_charge')->default(0);
            $table->string('status')->default('active');
            $table->string('suspended_reason')->nullable();
            $table->timestamp('username_changed_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('stores'); }
};
