<?php

namespace App\Http\Controllers;

use App\Models\DayMeal;
use App\Models\Ingredient;
use App\Models\Meal;
use App\Models\User;
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

        $user = User::with('activity')->find($user_id);

        if($user->sex = 1){
            $metabolism = (10 * $user->current_weight) + (6.25 * $user->height) - (5 * $user->age) - 10;
        } else {
            $metabolism = (10 * $user->current_weight) + (6.25 * $user->height) - (5 * $user->age) + 5;
        }

        $maintenance = $user->activity->multiplicator * $metabolism;

        if($user->goal_weight >= $user->current_weight){
            $goal_kcal = $maintenance * 1.20;
        } else {
            $goal_kcal = $maintenance * 0.85;
        }

        $goal_kcal = round($goal_kcal, 2);
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

        return [$kcal_per_day, $goal_kcal];
        // RESULT
        // {
        //     "1": 2200.58,
        //     "2": 1800.58,
        //     "3": 1320.58,
        //     "4": 1320.58,
        //     "5": 1320.58,
        //     "6": 1320.58,
        //     "7": 1720.58
        // }
    }

    public function all_meals_calories($user_id, $day_id){
        $user_meals_and_ingredients = Meal::where('user_id', $user_id)->with('ingredients')->with('days')->get();
        $kcal_per_meal = [];

        foreach($user_meals_and_ingredients as $meal){
            foreach($meal->ingredients as $ingredient){

                $multiplicator = $ingredient->portion / 100;
                $kcal_per_ingredient = $ingredient->calories * $multiplicator;

                if(array_key_exists($meal->name, $kcal_per_meal)){
                    $kcal_per_meal[$meal->name][0] += round($kcal_per_ingredient, 2);
                } else {
                    $kcal_per_meal[$meal->name][0] = round($kcal_per_ingredient, 2);
                }

                if(isset($day_id)){
                    foreach($meal->days as $day){

                        if($day->id == $day_id){
                            $kcal_per_meal[$meal->name][1] = 1;
                        } elseif($day->id != $day_id && !isset($kcal_per_meal[$meal->name][1])) {
                            $kcal_per_meal[$meal->name][1] = 0;
                        }
                    }
                }
            }
        }
        return $kcal_per_meal;

        // RESULT
        // {
        //     "Start week breakfast maxou": [480, 1],
        //     "All week Lunch maxou": [1320.58, 1],
        //     "Diner maxou": [400, 0]
        // }
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
        // RESULT
        // the meal + all his ingredients details
    }

    public function daily_meals($user_id, $day_id){

        $user_meals_and_ingredients = Meal::where('user_id', $user_id)->with('ingredients')->with('days')->get();
        $kcal_per_meal = [];

        foreach($user_meals_and_ingredients as $meal){
            foreach($meal->days as $day_meal){
                if($day_meal->id == $day_id){
                    foreach($meal->ingredients as $ingredient){

                        $multiplicator = $ingredient->portion / 100;
                        $kcal_per_ingredient = $ingredient->calories * $multiplicator;

                        if(array_key_exists($meal->name, $kcal_per_meal)){
                            $kcal_per_meal[$meal->name] += round($kcal_per_ingredient, 2);
                        } else {
                            $kcal_per_meal[$meal->name] = round($kcal_per_ingredient, 2);
                        }
                    }
                }
            }
        }
        $test = array_sum($kcal_per_meal);

        return $kcal_per_meal;
    }

    public function create(Request $request, $user_id)
    {
        // $data = $request->validate([
        //     'name' => 'required',
        // ]);

        $data['name'] = 'test repas 4';
        $data['user_id'] = 2;


        $meal = Meal::create([
            'name' => $data['name'],
            'user_id' => $user_id,
        ]);

        // $data2 = $request->validate([
        //     'ing_name' => 'array',
        //     'calories' => 'array',
        //     'proteins' => 'array',
        //     'glucides' => 'array',
        //     'lipids' => 'array',
        //     'portion' => 'array',
        // ]);

        // $ingredients_name = $request->input('ing_name');
        $ingredients_name = [1, 2];

        $ingredients = [];

        $data2['ing_name'] = ['vive les pates', 'vive le poulet'];
        $data2['calories'] = [200, 300];
        $data2['proteins'] = [20, 30];
        $data2['glucides'] = [24, 34];
        $data2['lipids'] = [2, 3];
        $data2['portion'] = [220, 320];

        if(!empty($data2)){
            $count = 0;
            foreach($ingredients_name as $ingredient_name){
                array_push($ingredients, Ingredient::create([
                    'name' => $data2['ing_name'][$count],
                    'meal_id' => $meal->id,
                    'calories' => $data2['calories'][$count],
                    'proteins' => $data2['proteins'][$count],
                    'glucides' => $data2['glucides'][$count],
                    'lipids' => $data2['lipids'][$count],
                    'portion' => $data2['portion'][$count],
                ]));
                $count++;
            }
        }
        return [$meal, $ingredients];
    }

    public function update_meal(Request $request, $user_id, $meal_id)
    {
         // $data = $request->validate([
        //     'name' => 'required',
        // ]);

        $data['name'] = 'test modification ouas';

        $meal = Meal::where('id', $meal_id)->update([
            'name' => $data['name'],
        ]);

        // $data2 = $request->validate([
        //     'existing_ing_name' => 'array',
        //     'id' => 'array',
        //     'calories' => 'array',
        //     'proteins' => 'array',
        //     'glucides' => 'array',
        //     'lipids' => 'array',
        //     'portion' => 'array',
        //     'deleted_at' => 'array',
        // ]);

        $data2['existing_ing_name'] = ['Poulet grillo', 'Pâtes grilla'];
        $data2['id'] = [1, 2];
        $data2['calories'] = [300, 300];
        $data2['proteins'] = [20, 30];
        $data2['glucides'] = [24, 34];
        $data2['lipids'] = [2, 3];
        $data2['portion'] = [220, 320];
        $data2['deleted_at'] = [1, 2];

        // $existing_ingredients_name = $request->input('existing_ing_name');
        $existing_ingredients_name = [1, 2];

        $ingredients = [];

        if(!empty($data2)){
            $count = 0;
            foreach($existing_ingredients_name as $ingredient_name){
                array_push($ingredients, Ingredient::where('id', $data2['id'][$count])->update([
                    'name' => $data2['existing_ing_name'][$count],
                    'meal_id' => $meal_id,
                    'calories' => $data2['calories'][$count],
                    'proteins' => $data2['proteins'][$count],
                    'glucides' => $data2['glucides'][$count],
                    'lipids' => $data2['lipids'][$count],
                    'portion' => $data2['portion'][$count],
                ]));
                $count++;
            }

            // $deleted_at = $request->input('deleted_at');
            $deleted_at = [1, 2];
            $count_delete = 0;
        }

        if(!empty($data2['deleted_at'])){
            foreach($deleted_at as $deleted){
                Ingredient::where('id', $data2['deleted_at'][$count_delete])->delete();
                $count_delete++;
            }
        }

        // $data3 = $request->validate([
        //     'new_ing_name' => 'array',
        //     'calories' => 'array',
        //     'proteins' => 'array',
        //     'glucides' => 'array',
        //     'lipids' => 'array',
        //     'portion' => 'array',
        // ]);

         // $new_ingredients_name = $request->input('new_ing_name');
        $new_ingredients_name = [1, 2];
        $new_ingredients = [];

        $data3['new_ing_name'] = ['POTATOZ', 'CREMEEEE'];
        $data3['calories'] = [100, 400];
        $data3['proteins'] = [10, 40];
        $data3['glucides'] = [14, 44];
        $data3['lipids'] = [1, 4];
        $data3['portion'] = [120, 420];

        if(!empty($data3)){
            $count_new = 0;
            foreach($new_ingredients_name as $ingredient_name){
                array_push($ingredients, Ingredient::create([
                    'name' => $data3['new_ing_name'][$count_new],
                    'meal_id' => $meal_id,
                    'calories' => $data3['calories'][$count_new],
                    'proteins' => $data3['proteins'][$count_new],
                    'glucides' => $data3['glucides'][$count_new],
                    'lipids' => $data3['lipids'][$count_new],
                    'portion' => $data3['portion'][$count_new],
                ]));
                $count_new++;
            }
        }

        return [$meal, $ingredients, $new_ingredients];
    }
}
