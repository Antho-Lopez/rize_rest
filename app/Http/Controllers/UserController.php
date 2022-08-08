<?php

namespace App\Http\Controllers;

use App\Models\OldWeight;
use App\Models\User;
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
        return User::with(['meals', 'trainings', 'sleeps'])->find($id);
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
