<?php

namespace App\Http\Controllers;

use App\Models\Day;
use App\Models\Sleep;
use Illuminate\Http\Request;

class SleepController extends Controller
{
   /**
    * It returns all the sleep records for a given user, ordered by the day_id
    *
    * param user_id The id of the user whose sleep data you want to retrieve.
    *
    * return A collection of all the sleeps for a user.
    */
    public function index($user_id)
    {
        $user_sleeps = Sleep::where('user_id', $user_id)->orderBy('day_id', 'ASC')->get();
        return $user_sleeps;
    }

    /**
     * It checks if the day_id exists in the database, if it does, it updates the data, if it doesn't,
     * it creates a new entry
     *
     * param Request request The request object.
     * param user_id The id of the user that is logged in.
     */
    public function update(Request $request, $user_id)
    {
        $data_days = Sleep::where('user_id', $user_id)->get('day_id');
        $get_days = [];

        foreach($data_days as $days){
            array_push($get_days, $days->day_id);
        }

        $data = $request->validate([
            'day_id' => 'array',
            'go_to_sleep' => '',
            'waking_up' => '',
        ]);

        $days_id = $request->input('day_id');
        $count = 0;

        foreach($days_id as $day_id){

            if(in_array($day_id, $get_days)){

                Sleep::where('user_id', $user_id)->where('day_id', $data['day_id'][$count])->update([
                    'go_to_sleep' => $data['go_to_sleep'],
                    'waking_up' => $data['waking_up'],
                ]);
                $count++;

            } else {

                Sleep::create([
                    'day_id' => $day_id,
                    'user_id' => $user_id,
                    'go_to_sleep' => $data['go_to_sleep'],
                    'waking_up' => $data['waking_up'],
                ]);
                $count++;

            }
        }
    }
}
