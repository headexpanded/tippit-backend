// Mini League Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('mini-leagues', MiniLeagueController::class);
    Route::post('mini-leagues/{miniLeague}/members', [MiniLeagueController::class, 'addMember']);
    Route::delete('mini-leagues/{miniLeague}/members', [MiniLeagueController::class, 'removeMember']);
    Route::post('mini-leagues/{miniLeague}/leave', [MiniLeagueController::class, 'leave']);
    Route::get('mini-leagues/{miniLeague}/rankings', [MiniLeagueController::class, 'getRankings']);
});

// User Routes
Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [UserController::class, 'logout']);
    Route::get('profile', [UserController::class, 'profile']);
    Route::put('profile', [UserController::class, 'updateProfile']);
    Route::get('predictions', [UserController::class, 'getPredictions']);
    Route::get('mini-leagues', [UserController::class, 'getMiniLeagues']);

    // Statistics Routes
    Route::get('statistics/user', [StatisticsController::class, 'getUserStatistics']);
    Route::get('statistics/rankings', [StatisticsController::class, 'getGlobalRankings']);
    Route::get('statistics/site', [StatisticsController::class, 'getSiteStatistics']);
    Route::get('statistics/season/{season}', [StatisticsController::class, 'getSeasonStatistics']);
    Route::get('statistics/accuracy', [StatisticsController::class, 'getPredictionAccuracy']);
});
