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

        // $data = $request->validate([
        //     'day_id' => 'array',
        //     'go_to_sleep' => 'array',
        //     'waking_up' => 'array',
        // ]);

        // $days_id = $request->input('day_id');
        $days_id = [1, 4 , 6];
        $count = 0;

        $data['day_id'] = [1, 4, 6];
        $data['go_to_sleep'] = ['21:00:00', '22:00:00', '23:00:00'];
        $data['waking_up'] = ['11:00:00', '12:00:00', '13:00:00'];

        foreach($days_id as $day_id){

            if(in_array($day_id, $get_days)){

                dump('update', $data['go_to_sleep'][$count], $data['waking_up'][$count], $data['day_id'][$count]);

                Sleep::where('user_id', $user_id)->where('day_id', $data['day_id'][$count])->update([
                    'go_to_sleep' => $data['go_to_sleep'][$count],
                    'waking_up' => $data['waking_up'][$count],
                ]);
                $count++;

            } else {

                dump('create' , $data['go_to_sleep'][$count], $data['waking_up'][$count], $data['day_id'][$count]);

                Sleep::create([
                    'day_id' => $day_id,
                    'user_id' => $user_id,
                    'go_to_sleep' => $data['go_to_sleep'][$count],
                    'waking_up' => $data['waking_up'][$count],
                ]);
                $count++;

            }
        }
    }
}
