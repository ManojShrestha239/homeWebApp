<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table): void {
            // Google's stable user ID. Unique, so one Google account maps to one member
            $table->string('google_id')->nullable()->unique()->after('email');

            // Profile picture from Google (optional)
            $table->string('avatar_url', 500)->nullable();

            // Google-only members have no password, so this column must allow NULL
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Note: rolling back fails if Google-only members (with NULL password) exist
        Schema::table('members', function (Blueprint $table): void {
            $table->dropColumn(['google_id', 'avatar_url']);
        });
    }
};
