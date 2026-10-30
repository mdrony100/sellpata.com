<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('drafts', function (Blueprint $table) {
            $table->foreignId('page_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });

        // Old page URLs keep working after a slug change.
        Schema::create('page_redirects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('slug');
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['store_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_redirects');
        Schema::table('drafts', fn (Blueprint $t) => $t->dropConstrainedForeignId('page_id'));
    }
};
