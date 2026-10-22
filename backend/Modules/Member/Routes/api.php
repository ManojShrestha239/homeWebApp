<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Member\Http\Controllers\LoginMemberController;
use Modules\Member\Http\Controllers\MeController;
use Modules\Member\Http\Controllers\RegisterMemberController;
use Modules\Member\Http\Controllers\VerifyMemberEmailController;

// POST /api/v1/members/register
Route::post('/members/register', RegisterMemberController::class)
    ->name('members.register')
    ->middleware('throttle:10,1');

// GET /api/v1/members/verify?token=...
Route::get('/members/verify', VerifyMemberEmailController::class)
    ->name('members.verify')
    ->middleware('throttle:20,1');

// POST /api/v1/members/login
Route::post('/members/login', LoginMemberController::class)
    ->name('members.login')
    ->middleware('throttle:5,1');   // 5 attempts per minute slows down password guessing

// GET /api/v1/members/me   (needs a valid token)
Route::get('/members/me', MeController::class)
    ->name('members.me');
