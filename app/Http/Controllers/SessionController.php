<?php

namespace App\Http\Controllers;

use App\Models\Muscle;
use App\Models\Session;
use Illuminate\Http\Request;

class SessionController extends Controller
{

    /**
     * I get all the sessions of a training, then I get all the repetitions of each session, then I sum
     * the repetitions of each session and I return an array with the sum of the repetitions and the
     * date of the session
     *
     * param training_id the id of the training
     */
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

    public function show($training_id, $session_id)
    {
        $one_session = Session::with('repetitions.exercice.muscle')->find($session_id);
        $muscles_training = Muscle::where('training_id', $training_id)->with('exercices.repetitions.session')->get();

        return $muscles_training;
    }

}
