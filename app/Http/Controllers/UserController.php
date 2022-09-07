<?php

namespace App\Http\Controllers;

use App\Models\OldWeight;
use App\Models\User;
use App\Models\Sleep;
use App\Models\Training;
use App\Models\Meal;
use App\Models\DayTraining;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    /**
     * It creates a new user in the database, creates a token for that user, and returns the token to
     * the client
     *
     * param Request request The request object.
     *
     * return The access token and the token type.
     */
    public function register(Request $request){

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'activity_id' => '',
            'email' => 'required|string|email|max:255|unique:users',
            'sex' => '',
            'birth_date' => '',
            'age' => '',
            'height' => '',
            'current_weight' => '',
            'goal_weight' => '',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'activity_id' => $validatedData['activity_id'],
            'email' => $validatedData['email'],
            'sex' => $validatedData['sex'],
            'birth_date' => $validatedData['birth_date'],
            'age' => $validatedData['age'],
            'height' => $validatedData['height'],
            'current_weight' => $validatedData['current_weight'],
            'goal_weight' => $validatedData['goal_weight'],
            'password' => Hash::make($validatedData['password']),
        ]);


        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * If the user's email and password match the ones in the database, then create a token and return
     * it
     *
     * param Request request The request object.
     *
     * return A token is being returned.
     */
    public function login(Request $request){

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid login details'], 401);
        }

        $user = User::where('email', $request['email'])->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * It returns the user that is currently authenticated
     *
     * param Request request The incoming request object.
     *
     * return The user that is currently logged in.
     */
    public function me(Request $request)
    {
        return $request->user();
    }

    /**
     * It finds the user with the given id, and then returns the user with all of their meals,
     * trainings, and sleeps
     *
     * param id The id of the user you want to show
     *
     * return The user with the id of , and all of the meals, trainings, and sleeps associated with
     * that user.
     */
    public function show($id)
    {
        $user = User::with(['meals', 'trainings', 'sleeps'])->find($id);
        return $user;
    }

    /**
     * It returns an array with the user, the last measured weight, the sleep, the training, the
     * calories and the day
     *
     * param id user id
     *
     * return An array with the user, last measured weight, today's sleep, today's training, today's
     * calories and today's day.
     */
    public function home($id)
    {
        $today = 1;
        $today_day = Date('l');

        switch ($today_day) {
            case 'Monday':
                $today = 1;
                break;
            case 'Tuesday':
                $today = 2;
                break;
            case 'Wednesday':
                $today = 3;
                break;
            case 'Thursday':
                $today = 4;
                break;
            case 'Friday':
                $today = 5;
                break;
            case 'Saturday':
                $today = 6;
                break;
            case 'Sunday':
                $today = 7;
                break;
        };

        $user = User::find($id);
        $last_measured_weight = OldWeight::where('user_id', $id)->orderBy('created_at', 'desc')->first();
        $today_sleep = Sleep::where('user_id', $id)->where('day_id', $today)->first();
        $training = Training::where('user_id', $id)->whereRelation('days', 'id', '=', $today)->first();
        $user_meals_and_ingredients = Meal::where('user_id', $id)->with('ingredients')->with('days')->get();
        $kcal_per_meal = [];

        foreach($user_meals_and_ingredients as $meal){
            foreach($meal->days as $day_meal){
                if($day_meal->id == $today){
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

        $today_calories = array_sum($kcal_per_meal);
        return [$user, $last_measured_weight, $today_sleep, $training, $today_calories, $today];
    }

    /**
     * If the user's last weight entry was today, update the user's current weight and the last weight
     * entry. If the user's last weight entry was not today, update the user's current weight and
     * create a new weight entry
     *
     * param Request request The request object.
     * param id The id of the user you want to update.
     *
     * return The user's current weight is being updated.
     */
    public function update_weight(Request $request, $id)
    {

        User::where('id', $id)->first();
        $get_last_weight = OldWeight::where('user_id', $id)->orderBy('created_at', 'desc')->first();
        $today = date('Y-m-d');
        $last_weight = date_format($get_last_weight->created_at,'Y-m-d');

        $weightData = $request->validate([
            'current_weight' => "required",
        ]);

        if($last_weight == $today){

            return response()->json([
                User::where('id', $id)->update([
                    'current_weight' =>  $weightData['current_weight'],
                ]),

                OldWeight::where('id', $get_last_weight->id)->update([
                    'weight' =>  $weightData['current_weight'],
                ]),
            ]);

        } else {

            return response()->json([
                User::where('id', $id)->update([
                    'current_weight' =>  $weightData['current_weight'],
                ]),

                OldWeight::where('user_id', $id)->create([
                    'user_id' => $id,
                    'weight' => $weightData['current_weight'],
                    'created_at' => date('Y-m-d H:i:s'),
                ]),
            ]);
        }
    }

}
