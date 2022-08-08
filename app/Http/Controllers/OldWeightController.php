<?php

namespace App\Http\Controllers;

use App\Models\OldWeight;
use App\Models\User;
use Illuminate\Http\Request;

class OldWeightController extends Controller
{
    public function show($user_id)
    {
        $user = User::find($user_id);
        $user_weights = OldWeight::where('user_id', $user_id)->get();

        return [$user, $user_weights];
    }
}
