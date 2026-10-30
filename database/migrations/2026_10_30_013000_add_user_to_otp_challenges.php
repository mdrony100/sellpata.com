<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('otp_challenges', function (Blueprint $table) {
            // phone changes are verified for a signed-in user, not a draft
            $table->foreignId('user_id')->nullable()->after('anonymous_identity_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('otp_challenges', fn (Blueprint $t) => $t->dropConstrainedForeignId('user_id'));
    }
};
