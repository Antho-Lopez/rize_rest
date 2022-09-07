<?php

namespace App\Http\Controllers;

use App\Models\OldWeight;
use App\Models\User;
use Illuminate\Http\Request;

class OldWeightController extends Controller
{
   /**
    * It takes a user_id, finds the user, and then finds all the weights associated with that user
    *
    * param user_id The id of the user you want to get the data for.
    *
    * return An array of the user and the user's weights.
    */
    public function show($user_id)
    {
        $user = User::find($user_id);
        $user_weights = OldWeight::where('user_id', $user_id)->orderBy('created_at', 'DESC')->get();

        return [$user, $user_weights];
    }
}
