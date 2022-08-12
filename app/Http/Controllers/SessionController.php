<?php

namespace App\Http\Controllers;

use App\Models\Session;
use Illuminate\Http\Request;

class SessionController extends Controller
{

    public function index($training_id)
    {
        $all_sessions = Session::where('training_id', $training_id)->with('repetitions')->get();

        $kilos_lifted = [];
        $count_key = 0;

        foreach($all_sessions as $one_session){
            foreach($one_session->repetitions as $repetition){
                if(array_key_exists($count_key, $kilos_lifted)){

                    $kilos_lifted[$count_key][0] += round($repetition->nb_repetitions * $repetition->kilos, 2);
                } else {
                    $kilos_lifted[$count_key][0] = round($repetition->nb_repetitions * $repetition->kilos, 2);
                }
            }
            $kilos_lifted[$count_key][1] = $one_session->session_day;
            $count_key++;
        }
        return $kilos_lifted;
    }

    public function show($training_id)
    {

    }

}
