<?php

declare(strict_types=1);

namespace Modules\Member\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Tells Laravel that this module has its own migrations folder.
 * Think of it as the module's "plug" that connects into the wall socket.
 */
final class MemberServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
}
