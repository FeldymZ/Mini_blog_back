<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\CommentaireController;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| Routes API - Mini Blog
|--------------------------------------------------------------------------
*/

//  Authentification
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->get('/me', [AuthController::class, 'me']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

//  Articles publics
Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{article}', [ArticleController::class, 'show']);

//  Commentaires publics
Route::get('/articles/{id}/commentaires', [CommentaireController::class, 'index']);
Route::post('/commentaires', [CommentaireController::class, 'store']);

//  Routes protégées : admin uniquement
Route::middleware(['auth:sanctum', 'isAdmin'])->group(function () {
    Route::apiResource('articles', ArticleController::class)->except(['index', 'show']);
    Route::put('/commentaires/{id}/valider', [CommentaireController::class, 'valider']);
    Route::delete('/commentaires/{id}', [CommentaireController::class, 'destroy']);

    // Exemple route protégée
    Route::get('/admin-only', function () {
        return response()->json(['message' => 'Bienvenue admin 👑']);
    });
});
