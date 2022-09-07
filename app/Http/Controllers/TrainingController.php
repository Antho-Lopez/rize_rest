<?php

namespace App\Http\Controllers;

use App\Models\Training;
use Illuminate\Http\Request;

class TrainingController extends Controller
{
    /**
     * It returns all the trainings of a user with the days of each training.
     *
     * param user_id the id of the user
     */
    public function index($user_id)
    {
        $user_trainings = Training::where('user_id', $user_id)->with('days')->get();
        return $user_trainings;
    }

    /**
     * It takes the training id, gets the training with its sessions and repetitions, then calculates
     * the progression of the training
     *
     * param training_id the id of the training you want to show
     *
     * return The training with the four last sessions and the repetitions of each session.
     */
    public function show($training_id)
    {
        $training = Training::with('four_last_sessions.repetitions')->with('muscles.exercices')->find($training_id);
        $kilos_lifted = [];
        $count_key = 0;
        $previous_key = 0;


        function getPrevValue($key, $hash = array())
        {
            $keys = array_keys($hash);
            $found_index = array_search($key, $keys);
            if ($found_index === false || $found_index === 0)
                return false;
            return $hash[$keys[$found_index-1]];
        }

        foreach($training->four_last_sessions as $one_session){

            foreach($one_session->repetitions as $repetition){
                if(array_key_exists($count_key, $kilos_lifted)){

                    $kilos_lifted[$count_key][0] += round($repetition->nb_repetitions * $repetition->kilos, 2);
                } else {
                    $kilos_lifted[$count_key][0] = round($repetition->nb_repetitions * $repetition->kilos, 2);
                }
            }
            $kilos_lifted[$count_key][1] = $one_session->session_day;

            $progression = 0;
            if(getPrevValue($count_key, $kilos_lifted) != false){

                $for_calcul = getPrevValue($count_key, $kilos_lifted);
                $prev = array_shift($for_calcul);
                $progression = $prev - $kilos_lifted[$count_key][0];
            }

            if($count_key > 0){
                $previous_key = $count_key - 1;
            }
            $kilos_lifted[$previous_key][2] = round($progression, 2);
            $count_key++;

        }
        return [$training, $kilos_lifted];
    }

}
