<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('api')->group(function() {

    // USERS
    Route::post('/register', 'UserController@register');
    Route::post('/login', 'UserController@login');
    Route::post('/me', 'UserController@me')->middleware('auth:sanctum');
    Route::get('/user/{id}/show', 'UserController@show');
    Route::get('/user/{id}/weight', 'OldWeightController@show');
    Route::post('/user/{id}/update_weight', 'UserController@update_weight');

    // SLEEPS
    Route::get('/user/{user_id}/sleeps', 'SleepController@index');
    Route::post('/user/{user_id}/update_sleep', 'SleepController@update');

    // MEALS
    Route::get('/user/{user_id}/meals', 'MealController@index');
    Route::get('/user/{user_id}/weekly_calories', 'MealController@weekly_calories');
    Route::get('/user/{user_id}/meals_calories', 'MealController@all_meals_calories');
    Route::get('/meal/{meal_id}/show', 'MealController@show');
    Route::get('/user/{user_id}/day/{day_id}', 'MealController@daily_meals');
});
