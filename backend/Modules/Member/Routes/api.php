<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Member\Http\Controllers\RegisterMemberController;

// POST /api/v1/members/register
Route::post('/members/register', RegisterMemberController::class)
    ->name('members.register')
    ->middleware('throttle:10,1');   // at most 10 attempts per minute per IP, otherwise 429
