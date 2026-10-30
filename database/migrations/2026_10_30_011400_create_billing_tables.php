<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained();
            $table->string('status')->default('active');
            $table->string('source')->default('free');
            $table->string('billing_period')->default('monthly');
            $table->timestamp('started_at');
            $table->timestamp('ends_at')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note')->nullable();
            $table->timestamps();
            $table->index(['store_id', 'status']);
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->string('type');
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('amount');
            $table->string('currency', 3)->default('BDT');
            $table->string('status')->default('unpaid');
            $table->string('payment_reference')->nullable();
            $table->json('seller_snapshot');
            $table->json('buyer_snapshot');
            $table->timestamp('issued_at');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('invoices'); Schema::dropIfExists('subscriptions'); }
};
