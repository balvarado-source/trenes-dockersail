<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route; 
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\CommentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/hello', function () {
    return response()->json(['message' => 'Hola trenes desde docker Sail!']);
});


Route::post('/register', [\App\Http\Controllers\AuthController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);

    
    
//auth:sanctum
Route::middleware('auth0')->group(function () {
    Route::get('/logout',[App\Http\Controllers\AuthController::class, 'logout']);
    Route::apiResource('/posts', PostController::class);
    Route::apiResource('/comments', CommentController::class);

});


// Route::post('/auth0-user', function (Request $request) {
//     $data = $request->validate([
//         'auth0_id' => 'required|string',
//         'name' => 'required|string',
//         'email' => 'required|email',
//         'avatar' => 'nullable|string'
//     ]);

//     $user = User::firstOrCreate(
//         ['auth0_id' => $data['auth0_id']],
//         [
//             'name' => $data['name'],
//             'email' => $data['email'],
//             'avatar' => $data['avatar'],
//             'password' => bcrypt(str()->random(16)),
//         ]
//     );

//     return response()->json($user);
// });

