<?php

namespace App\Http\Controllers;

use App\Models\DayMeal;
use App\Models\Ingredient;
use App\Models\Meal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MealController extends Controller
{
   /**
    * This function returns all the meals for a given user
    *
    * user_id The id of the user whose meals we want to retrieve.
    *
    * return a collection of all the meals for a specific user.
    */
    public function index($user_id)
    {
        $user_meals = Meal::where('user_id', $user_id)->get();
        return $user_meals;
    }

    /**
     * It takes a user_id, gets all the meals and ingredients of the user, calculates the user's
     * metabolism, calculates the user's goal kcal, calculates the user's proteins and glucides per
     * day, calculates the kcal per meal, groups the meals per day, calculates the kcal per day and
     * returns an array with the kcal per day, the goal kcal, the proteins per day, the glucides per
     * day and the user's current weight
     *
     * param user_id the id of the user
     *
     * return an array with the following values:
     * - : an array with the kcal per day
     * - : the goal kcal per day
     * - : the proteins per day
     * - : the glucides per day
     * - ->current_weight: the user's
     */
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
        $proteins_per_day = $user->current_weight * 2;
        $glucides_per_day = $user->current_weight * 4;
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

        return [$kcal_per_day, $goal_kcal, $proteins_per_day, $glucides_per_day, $user->current_weight];
    }

    /**
     * It takes a user_id and a day_id as parameters and returns an array of all the meals of the user
     * with the total calories of each meal
     *
     * param user_id the id of the user
     * param day_id the id of the day you want to get the meals from. If you don't pass this
     * parameter, the function will return all the meals from all the days.
     *
     * return An array of meals with their kcal values.
     */
    public function all_meals_calories($user_id, $day_id = null){

        if($day_id){
            $user_meals_and_ingredients = Meal::where('user_id', $user_id)->with('ingredients')->with('days')->whereRelation('days', 'id', '=', $day_id)->get();
            $user_meals = Meal::where('user_id', $user_id)->whereRelation('days', 'id', '=', $day_id)->get();
        } else {
            $user_meals_and_ingredients = Meal::where('user_id', $user_id)->with('ingredients')->with('days')->get();
            $user_meals = Meal::where('user_id', $user_id)->get();
        }

        $kcal_per_meal = [];
        $count = 0;


        foreach($user_meals_and_ingredients as $meal){

            if(count($meal->ingredients) > 0){

                foreach($meal->ingredients as $ingredient){

                    $multiplicator = $ingredient->portion / 100;
                    $kcal_per_ingredient = $ingredient->calories * $multiplicator;

                    if(array_key_exists($count, $kcal_per_meal)){
                        $kcal_per_meal[$count] += round($kcal_per_ingredient, 2);
                    } else {
                        $kcal_per_meal[$count] = round($kcal_per_ingredient, 2);
                    }
                }

            } else {

                $kcal_per_meal[$count] = 0;

            }

            $user_meals[$count]['kcal'] = $kcal_per_meal[$count];
            $count++;

        }

        return $user_meals;
    }

    /**
     * It takes a meal id, fetches the meal from the database, multiplies the nutritional values of
     * each ingredient by the portion size, and returns the meal
     *
     * param meal_id The id of the meal you want to show
     *
     * return A meal with its ingredients and their nutritional values.
     */
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

    /**
     * It takes a user_id and a day_id as arguments and returns an array with the names of the meals as
     * keys and the sum of the calories of the ingredients of the meal as values
     *
     * param user_id the id of the user
     * param day_id 1, 2, 3, 4, 5, 6, 7
     *
     * return An array with the name of the meal as key and the kcal as value.
     */
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

    /**
     * It gets all the meals that are already in the day and all the meals that are not in the day
     *
     * param user_id the id of the user
     * param day_id the id of the day you want to edit
     *
     * return An array with two arrays. The first array contains all the meals that are already in the
     * day. The second array contains all the meals that are not in the day.
     */
    public function edit_day_meal($user_id, $day_id){

        $meals_already_in = Meal::where('user_id', $user_id)->with('ingredients')->with('days')->whereRelation('days', 'id', '=', $day_id)->get();
        $all_meals = Meal::where('user_id', $user_id)->with('ingredients')->with('days')->get();
        $count = 0;
        $count_meal_already_in = 0;
        $count2 = 0;
        $kcal_per_meal = [];
        $kcal_per_meal_already_in = [];

        foreach($all_meals as $meal){
            if(count($meal->ingredients) > 0){
                foreach($meal->ingredients as $ingredient){

                    $multiplicator = $ingredient->portion / 100;
                    $kcal_per_ingredient = $ingredient->calories * $multiplicator;

                    if(array_key_exists($count, $kcal_per_meal)){
                        $kcal_per_meal[$count] += round($kcal_per_ingredient, 2);
                    } else {
                        $kcal_per_meal[$count] = round($kcal_per_ingredient, 2);
                    }
                }
            } else {
                $kcal_per_meal[$count] = 0;
            }
            $all_meals[$count]['kcal'] = $kcal_per_meal[$count];
            $count++;
        }

        foreach($all_meals as $meal){
            foreach($meals_already_in as $meal_already_in){

                if($meal->id == $meal_already_in->id){
                    $all_meals->forget($count2);
                }
            }
            $count2++;
        }

        foreach($meals_already_in as $meal_already_in){

            if(count($meal_already_in->ingredients) > 0){
                foreach($meal_already_in->ingredients as $ingredient){

                    $multiplicator = $ingredient->portion / 100;
                    $kcal_per_ingredient = $ingredient->calories * $multiplicator;

                    if(array_key_exists($count_meal_already_in, $kcal_per_meal_already_in)){
                        $kcal_per_meal_already_in[$count_meal_already_in] += round($kcal_per_ingredient, 2);
                    } else {
                        $kcal_per_meal_already_in[$count_meal_already_in] = round($kcal_per_ingredient, 2);
                    }
                }
            } else {
                $kcal_per_meal_already_in[$count_meal_already_in] = 0;
            }
            $meals_already_in[$count_meal_already_in]['kcal'] = $kcal_per_meal_already_in[$count_meal_already_in];
            $count_meal_already_in++;
        }

        return [$meals_already_in, $all_meals->values()];
    }

    /**
     * It creates a meal and its ingredients
     *
     * param Request request The request object.
     * param user_id the id of the user who created the meal
     *
     * return The meal and the ingredients
     */
    public function create(Request $request, $user_id)
    {
        $data = $request->validate([
            'name' => 'required',
        ]);

        $meal = Meal::create([
            'name' => $data['name'],
            'user_id' => $user_id,
        ]);

        $data2 = $request->validate([
            'ing_name' => 'array',
            'calories' => 'array',
            'proteins' => 'array',
            'glucides' => 'array',
            'lipids' => 'array',
            'portion' => 'array',
        ]);

        $ingredients_name = $request->input('ing_name');
        $ingredients = [];


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

    /**
     * It updates a meal and its ingredients
     *
     * param Request request the request object
     * param user_id the id of the user who owns the meal
     * param meal_id the id of the meal you want to update
     *
     * return The meal, the ingredients and the new ingredients.
     */
    public function update_meal(Request $request, $user_id, $meal_id)
    {
        $data = $request->validate([
            'name' => 'required',
        ]);

        $meal = Meal::where('id', $meal_id)->update([
            'name' => $data['name'],
        ]);

        $data2 = $request->validate([
            'existing_ing_name' => 'array',
            'id' => 'array',
            'calories' => 'array',
            'proteins' => 'array',
            'glucides' => 'array',
            'lipids' => 'array',
            'portion' => 'array',
            'deleted_at' => 'array',
        ]);

        $existing_ingredients_name = $request->input('existing_ing_name');
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

            $deleted_at = $request->input('deleted_at');
            $count_delete = 0;
        }

        if(!empty($data2['deleted_at'])){
            foreach($deleted_at as $deleted){
                Ingredient::where('id', $data2['deleted_at'][$count_delete])->delete();
                $count_delete++;
            }
        }

        $data3 = $request->validate([
            'new_ing_name' => 'array',
            'calories' => 'array',
            'proteins' => 'array',
            'glucides' => 'array',
            'lipids' => 'array',
            'portion' => 'array',
        ]);

        $new_ingredients_name = $request->input('new_ing_name');
        $new_ingredients = [];


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

    /**
     * It takes in a request, a user_id, and a day_id, and then it updates the day_meal table with the
     * added_meals and removed_meals from the request
     *
     * param Request request The request object.
     * param user_id The user id of the user you want to update the meals for.
     * param day_id the id of the day you want to update
     */
    public function update_daily_meals(Request $request, $user_id, $day_id)
    {

        $data = $request->validate([
            'added_meals' => 'array',
            'removed_meals' => 'array',
            'day_id' => '',
        ]);

        $added_meals = $request->input('added_meals');
        $removed_meals = $request->input('removed_meals');

        foreach($added_meals as $added_meal){
            DB::table('day_meal')->insert([
                'day_id' => $day_id,
                'meal_id' => $added_meal,
            ]);
        }

        foreach($removed_meals as $removed_meal){
            DayMeal::where('day_id', $day_id)->where('meal_id', $removed_meal)->forceDelete();
        }


    }
}
