<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LeagueController;
use App\Http\Controllers\Api\RoundController;
use App\Http\Controllers\Api\StatisticsController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PredictionController;
use Illuminate\Support\Facades\Route;

// Public Routes

Route::apiResource('players', UserController::class);
Route::apiResource('leagues', LeagueController::class);

// League Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('leagues/{league}/members', [LeagueController::class, 'addMember']);
    Route::delete('leagues/{league}/members', [LeagueController::class, 'removeMember']);
    Route::post('leagues/{league}/leave', [LeagueController::class, 'leave']);
    Route::get('leagues/{league}/rankings', [LeagueController::class, 'getRankings']);
});

// User Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('profile', [UserController::class, 'profile']);
    Route::get('predictions', [UserController::class, 'getPredictions']);
    Route::get('user/leagues', [UserController::class, 'getLeagues']);

    // Account management
    Route::delete('account', [UserController::class, 'deleteAccount']);

    // Prediction Routes
    Route::apiResource('predictions', PredictionController::class);
    Route::get('games/{game}/predictions', [PredictionController::class, 'getGamePredictions']);
    Route::get('games/{game}/user-prediction', [PredictionController::class, 'getUserPredictionForGame']);

    // Statistics Routes
    Route::get('statistics/user', [StatisticsController::class, 'getUserStatistics']);
    Route::get('statistics/rankings', [StatisticsController::class, 'getGlobalRankings']);
    Route::get('statistics/site', [StatisticsController::class, 'getSiteStatistics']);
    Route::get('statistics/season/{season}', [StatisticsController::class, 'getSeasonStatistics']);
    Route::get('statistics/accuracy', [StatisticsController::class, 'getPredictionAccuracy']);
});

// Round Routes
Route::get('rounds', [RoundController::class, 'index']);
Route::get('rounds/{round}', [RoundController::class, 'show']);
Route::get('rounds/{round}/matches', [RoundController::class, 'matches']);
Route::get('rounds/{round}/statistics', [RoundController::class, 'statistics']);
Route::get('rounds/{round}/users/statistics', [RoundController::class, 'allUsersStatistics']);

// Next Round Route (public - no auth required)
Route::get('next-round', [RoundController::class, 'nextRound']);

// Protected Round Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('rounds/{round}/user-statistics/{user}', [RoundController::class, 'userStatistics']);
    Route::get('rounds/{round}/predictions/{user}', [RoundController::class, 'userPredictions']);
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
