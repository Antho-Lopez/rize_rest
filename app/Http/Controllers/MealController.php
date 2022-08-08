<?php

namespace App\Http\Controllers;

use App\Models\DayMeal;
use App\Models\Meal;
use Illuminate\Http\Request;

class MealController extends Controller
{
    public function index($user_id)
    {
        $user_meals = Meal::where('user_id', $user_id)->get();
        return $user_meals;
    }

    public function weekly_calories($user_id)
    {
        $user_meals_and_ingredients = Meal::where('user_id', $user_id)->with('ingredients')->get();
        $kcal_per_meal = [];
        $kcal_per_day = [];

        foreach($user_meals_and_ingredients as $meal){
            foreach($meal->ingredients as $ingredient){

                $multiplicator = $ingredient->portion / 100;
                $kcal_per_ingredient = $ingredient->calories * $multiplicator;

                if(array_key_exists($ingredient->meal_id, $kcal_per_meal)){
                    $kcal_per_meal[$ingredient->meal_id] += $kcal_per_ingredient;
                } else {
                    $kcal_per_meal[$ingredient->meal_id] = $kcal_per_ingredient;
                }
            }
        }
        $get_user_meals = DayMeal::whereIn('meal_id', array_keys($kcal_per_meal))->get();
        $group_meals_per_day = $get_user_meals->groupBy('day_id');

        foreach($group_meals_per_day as $meals_per_day){
            foreach($meals_per_day as $one_meal){
                if(array_key_exists($one_meal->day_id, $kcal_per_day)){
                    $kcal_per_meal[$one_meal->meal_id] = round($kcal_per_meal[$one_meal->meal_id], 2);
                    $kcal_per_day[$one_meal->day_id] += $kcal_per_meal[$one_meal->meal_id];

                } else {
                    $kcal_per_meal[$one_meal->meal_id] = round($kcal_per_meal[$one_meal->meal_id], 2);
                    $kcal_per_day[$one_meal->day_id] = $kcal_per_meal[$one_meal->meal_id];

                }
            }
        }
        // dump($group_meals_per_day);
        // dump($kcal_per_meal);
        // dump($kcal_per_day);
        return $kcal_per_day;
    }

    public function all_meals_calories($user_id){
        $user_meals_and_ingredients = Meal::where('user_id', $user_id)->with('ingredients')->get();
        $kcal_per_meal = [];

        foreach($user_meals_and_ingredients as $meal){
            foreach($meal->ingredients as $ingredient){

                $multiplicator = $ingredient->portion / 100;
                $kcal_per_ingredient = $ingredient->calories * $multiplicator;

                if(array_key_exists($meal->name, $kcal_per_meal)){
                    $kcal_per_meal[$meal->name] += $kcal_per_ingredient;
                } else {
                    $kcal_per_meal[$meal->name] = $kcal_per_ingredient;
                }
            }
        }
        return $kcal_per_meal;
    }

    public function show($meal_id){

        $meal = Meal::where('id', $meal_id)->with('ingredients')->first();

        foreach($meal->ingredients as $ingredient){
            $multiplicator = $ingredient->portion / 100;
            $ingredient->calories = round($ingredient->calories * $multiplicator, 2);
            $ingredient->proteins = round($ingredient->proteins * $multiplicator, 2);
            $ingredient->glucides = round($ingredient->glucides * $multiplicator, 2);
            $ingredient->lipids = round($ingredient->lipids * $multiplicator, 2);
        }
        return $meal;
    }

    public function daily_meals($user_id, $day_id){

        $user_meals = Meal::where('user_id', $user_id)->get();

    }
}
