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

    public function register(Request $request){

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'activity_id' => '',
            'email' => 'required|string|email|max:255|unique:users',
            'sex' => '',
            'birth_date' => '',
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

    public function me(Request $request)
    {
        return $request->user();
    }

    public function show($id)
    {
        $user = User::with(['meals', 'trainings', 'sleeps'])->find($id);
        return $user;
    }

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
        return [$user, $last_measured_weight, $today_sleep, $training, $today_calories];
    }

    public function update_weight(Request $request, $id)
    {

        User::where('id', $id)->first();
        $get_last_weight = OldWeight::where('user_id', $id)->orderBy('created_at', 'desc')->first();
        $today = date('Y-m-d');
        $last_weight = date_format($get_last_weight->created_at,'Y-m-d');

        $data = $request->validate([
            'current_weight' => "required",
        ]);

        if($last_weight == $today){

            return response()->json([
                User::where('id', $id)->update([
                    'current_weight' =>  $data['current_weight'],
                ]),

                OldWeight::where('id', $get_last_weight->id)->update([
                    'weight' =>  $data['current_weight'],
                ]),
            ]);

        } else {

            return response()->json([
                User::where('id', $id)->update([
                    'current_weight' =>  $data['current_weight'],
                ]),

                OldWeight::where('user_id', $id)->create([
                    'user_id' => $id,
                    'weight' => $data['current_weight'],
                    'created_at' => date('Y-m-d H:i:s'),
                ]),
            ]);
        }
    }

}
