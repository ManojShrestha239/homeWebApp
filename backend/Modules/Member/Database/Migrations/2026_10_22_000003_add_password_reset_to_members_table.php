<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table): void {
            // SHA-256 hash of the emailed token. Never the plain token.
            $table->string('password_reset_token', 64)->nullable()->index();
            $table->timestamp('password_reset_expires_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table): void {
            $table->dropColumn(['password_reset_token', 'password_reset_expires_at']);
        });
    }
};
