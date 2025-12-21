<?php

use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployerJobListingController;
use App\Http\Controllers\Api\JobListingController;
use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// public access
Route::get('/jobs', [JobListingController::class, 'index']);
Route::get('/jobs/{jobListing}', [JobListingController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead']);

    // for applicants
    Route::post('/jobs/{jobListing}/applications', [ApplicationController::class, 'store']);
    Route::get('/my-applications', [ApplicationController::class, 'index']);

    Route::prefix('employer')->group(function () {
        Route::get('/jobs', [EmployerJobListingController::class, 'index']);
        Route::post('/jobs', [EmployerJobListingController::class, 'store']);
        Route::get('/jobs/{jobListing}', [EmployerJobListingController::class, 'show']);
        Route::put('/jobs/{jobListing}', [EmployerJobListingController::class, 'update']);
        Route::delete('/jobs/{jobListing}', [EmployerJobListingController::class, 'destroy']);

        // for employment management
        Route::get('/job-listing', [ApplicationController::class, 'jobListing']);
        Route::get('/jobs/{jobListing}/applications', [ApplicationController::class, 'forJob']);
        Route::post('/applications/{application}/view', [ApplicationController::class, 'markAsViewed']);
        Route::post('/applications/{application}/shortlist', [ApplicationController::class, 'shortlist']);
        Route::post('/applications/{application}/reject', [ApplicationController::class, 'reject']);
    });
});
