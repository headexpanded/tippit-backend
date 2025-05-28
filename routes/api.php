<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MiniLeagueController;
use App\Http\Controllers\Api\StatisticsController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Mini League Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('mini-leagues', MiniLeagueController::class);
    Route::post('mini-leagues/{miniLeague}/members', [MiniLeagueController::class, 'addMember']);
    Route::delete('mini-leagues/{miniLeague}/members', [MiniLeagueController::class, 'removeMember']);
    Route::post('mini-leagues/{miniLeague}/leave', [MiniLeagueController::class, 'leave']);
    Route::get('mini-leagues/{miniLeague}/rankings', [MiniLeagueController::class, 'getRankings']);
});

// User Routes


Route::middleware('auth:sanctum')->group(function () {
    Route::get('profile', [UserController::class, 'profile']);
    Route::get('predictions', [UserController::class, 'getPredictions']);
    Route::get('mini-leagues', [UserController::class, 'getMiniLeagues']);

    // Statistics Routes
    Route::get('statistics/user', [StatisticsController::class, 'getUserStatistics']);
    Route::get('statistics/rankings', [StatisticsController::class, 'getGlobalRankings']);
    Route::get('statistics/site', [StatisticsController::class, 'getSiteStatistics']);
    Route::get('statistics/season/{season}', [StatisticsController::class, 'getSeasonStatistics']);
    Route::get('statistics/accuracy', [StatisticsController::class, 'getPredictionAccuracy']);
});

// OAuth Routes
Route::get('auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

// OTP Routes
Route::post('auth/otp/send', [AuthController::class, 'sendOtp']);
Route::post('auth/otp/verify', [AuthController::class, 'verifyOtp']);

// Passkey Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('auth/passkey/options', [AuthController::class, 'getPasskeyOptions']);
    Route::post('auth/passkey/register', [AuthController::class, 'registerPasskey']);
});

Route::get('auth/passkey/authenticate/options', [AuthController::class, 'getPasskeyAuthenticationOptions']);
Route::post('auth/passkey/authenticate', [AuthController::class, 'authenticateWithPasskey']);
