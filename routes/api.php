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
    Route::get('/user/{id}/home', 'UserController@home');
    Route::get('/user/{id}/weight', 'OldWeightController@show');
    Route::post('/user/{id}/update_weight', 'UserController@update_weight');

    // SLEEPS
    Route::get('/user/{user_id}/sleeps', 'SleepController@index');
    Route::post('/user/{user_id}/update_sleep', 'SleepController@update');

    // MEALS
    Route::get('/user/{user_id}/meals', 'MealController@index');
    Route::get('/user/{user_id}/weekly_calories', 'MealController@weekly_calories');
    Route::get('/user/{user_id}/meals_calories/{day_id?}', 'MealController@all_meals_calories');
    Route::get('/meal/{meal_id}/show', 'MealController@show');
    Route::get('/user/{user_id}/day/{day_id}', 'MealController@daily_meals');
    Route::post('/user/{user_id}/meal/create', 'MealController@create');
    Route::get('/user/{user_id}/day/{day_id}/edit', 'MealController@edit_day_meal');
    Route::post('/user/{user_id}/day/{day_id}/update', 'MealController@update_daily_meals');
    Route::post('/user/{user_id}/meal/{meal_id}/update_meal', 'MealController@update_meal');

    // TRAININGS
    Route::get('/user/{user_id}/trainings', 'TrainingController@index');
    Route::get('/training/{training_id}/show', 'TrainingController@show');
    Route::get('/training/{training_id}/sessions', 'SessionController@index');
    Route::get('/training/{training_id}/session/{session_id}/show', 'SessionController@show');
});
