<?php

namespace App\Http\Controllers;

use App\Models\Training;
use Illuminate\Http\Request;

class TrainingController extends Controller
{
    public function index($user_id)
    {
        $user_trainings = Training::where('user_id', $user_id)->with('days')->get();

        return $user_trainings;
    }
}
