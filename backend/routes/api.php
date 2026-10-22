<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// Every module's API routes go under the /api/v1 prefix
Route::prefix('v1')->group(base_path('Modules/Member/Routes/api.php'));
