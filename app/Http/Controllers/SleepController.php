<?php

namespace App\Http\Controllers;

use App\Models\Day;
use App\Models\Sleep;
use Illuminate\Http\Request;

class SleepController extends Controller
{
    public function index($user_id)
    {
        $user_sleeps = Sleep::where('user_id', $user_id)->get();
        return $user_sleeps;
    }

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
        // $days_id = [1, 2, 3];

        $count = 0;

        // $data['day_id'] = [1, 2, 3];
        // $data['go_to_sleep'] = '19:00:00';
        // $data['waking_up'] = '09:00:00';

        foreach($days_id as $day_id){

            if(in_array($day_id, $get_days)){

                dump('update', $data['go_to_sleep'], $data['waking_up'], $data['day_id'][$count]);

                Sleep::where('user_id', $user_id)->where('day_id', $data['day_id'][$count])->update([
                    'go_to_sleep' => $data['go_to_sleep'],
                    'waking_up' => $data['waking_up'],
                ]);
                $count++;

            } else {

                dump('create' , $data['go_to_sleep'], $data['waking_up'], $data['day_id'][$count]);

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
