<?php

use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\PolymorphicCommentController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\VideoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/hello', function () {
    return response()->json(['message' => 'Hola trenes desde docker Sail!']);
});

// Rutas existentes (relación directa)
Route::apiResource('/posts', PostController::class);
Route::apiResource('/comments', CommentController::class);

// Nuevas rutas para relación polimórfica
Route::apiResource('/images', ImageController::class);
Route::apiResource('/videos', VideoController::class);
Route::apiResource('/polymorphic-comments', PolymorphicCommentController::class);

// Rutas específicas para comentarios polimórficos
Route::get('/images/{imageId}/comments', [PolymorphicCommentController::class, 'getImageComments']);
Route::post('/images/{imageId}/comments', [PolymorphicCommentController::class, 'storeImageComment']);

Route::get('/videos/{videoId}/comments', [PolymorphicCommentController::class, 'getVideoComments']);
Route::post('/videos/{videoId}/comments', [PolymorphicCommentController::class, 'storeVideoComment']);
