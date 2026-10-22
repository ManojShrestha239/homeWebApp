<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table): void {
            $table->id();                                        // auto-increment primary key
            $table->string('full_name', 120);
            $table->string('email', 255)->unique();              // the DATABASE blocks duplicates (the real guard)
            $table->string('password');                          // stores a HASH, never plain text
            $table->timestamp('email_verified_at')->nullable();  // empty until the member verifies
            $table->string('email_verification_token', 64)      // sha256 hash of the emailed token
                ->nullable()
                ->index();                                     // fast lookup when verifying later
            $table->timestamp('email_verification_expires_at')->nullable();
            $table->timestamps();                                // created_at, updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
