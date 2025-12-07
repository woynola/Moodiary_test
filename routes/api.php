<?php

use App\Http\Controllers\Api\JournalApiController;
use App\Http\Controllers\Api\MoodApiController;
use App\Http\Controllers\Api\ChallengeApiController;
use App\Http\Controllers\Api\ForumApiController;
use App\Http\Controllers\Api\UserApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Journal API
    Route::apiResource('journals', JournalApiController::class);
    Route::post('/journals/{journal}/upload-media', [JournalApiController::class, 'uploadMedia']);
    Route::get('/journals/search', [JournalApiController::class, 'search']);
    Route::get('/journals/calendar/{year}/{month}', [JournalApiController::class, 'calendar']);

    // Mood API
    Route::apiResource('moods', MoodApiController::class);
    Route::get('/moods/stats/weekly', [MoodApiController::class, 'weeklyStats']);
    Route::get('/moods/stats/monthly', [MoodApiController::class, 'monthlyStats']);
    Route::get('/moods/insights', [MoodApiController::class, 'insights']);
    Route::get('/moods/trend', [MoodApiController::class, 'trend']);

    // Challenge API
    Route::apiResource('challenges', ChallengeApiController::class, ['only' => ['index', 'show']]);
    Route::post('/challenges/{challenge}/join', [ChallengeApiController::class, 'join']);
    Route::get('/my-challenges', [ChallengeApiController::class, 'myChallenges']);
    Route::post('/user-challenges/{userChallenge}/checkpoint', [ChallengeApiController::class, 'completeCheckpoint']);
    Route::post('/user-challenges/{userChallenge}/abandon', [ChallengeApiController::class, 'abandon']);

    // Forum API
    Route::apiResource('forum/posts', ForumApiController::class);
    Route::post('/forum/posts/{post}/comment', [ForumApiController::class, 'storeComment']);
    Route::post('/forum/posts/{post}/react', [ForumApiController::class, 'react']);
    Route::post('/forum/comments/{comment}/react', [ForumApiController::class, 'reactComment']);
    Route::post('/forum/{reportable}/report', [ForumApiController::class, 'report']);
    Route::get('/forum/categories', [ForumApiController::class, 'categories']);

    // User API
    Route::get('/user/profile', [UserApiController::class, 'profile']);
    Route::patch('/user/profile', [UserApiController::class, 'updateProfile']);
    Route::get('/user/badges', [UserApiController::class, 'badges']);
    Route::get('/user/activity', [UserApiController::class, 'activity']);
    Route::get('/user/leaderboard', [UserApiController::class, 'leaderboard']);
    Route::get('/user/rank', [UserApiController::class, 'rank']);
});
