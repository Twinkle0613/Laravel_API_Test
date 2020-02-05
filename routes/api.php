<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('register', 'AuthController@register');
Route::get('getToken', 'AuthController@login');

Route::group([
    'prefix' => 'v1',
    'as' => 'api.',
    'middleware' => ['auth:api']
], function () {
    Route::post('import', 'UserController@import')->name('import');
    Route::apiResource('user', 'UserController');
});



