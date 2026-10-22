<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Member\Http\Controllers\ForgotPasswordController;
use Modules\Member\Http\Controllers\LoginMemberController;
use Modules\Member\Http\Controllers\MeController;
use Modules\Member\Http\Controllers\RegisterMemberController;
use Modules\Member\Http\Controllers\VerifyMemberEmailController;
use Modules\Member\Http\Controllers\GoogleOAuthCallbackController;
use Modules\Member\Http\Controllers\GoogleOAuthRedirectController;
use Modules\Member\Http\Controllers\ResetPasswordController;

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

// oAuth
// GET /api/v1/members/oauth/google/redirect   → JSON { url }
Route::get('/members/oauth/google/redirect', GoogleOAuthRedirectController::class)
    ->name('members.oauth.google.redirect')
    ->middleware('throttle:10,1');

// GET /api/v1/members/oauth/google/callback   → 302 to the React app
Route::get('/members/oauth/google/callback', GoogleOAuthCallbackController::class)
    ->name('members.oauth.google.callback')
    ->middleware('throttle:20,1');

// POST /api/v1/members/password/forgot
Route::post('/members/password/forgot', ForgotPasswordController::class)
    ->name('members.password.forgot')
    ->middleware('throttle:5,1');    // slows down spamming someone's inbox

// POST /api/v1/members/password/reset
Route::post('/members/password/reset', ResetPasswordController::class)
    ->name('members.password.reset')
    ->middleware('throttle:10,1');   // slows down guessing tokens
