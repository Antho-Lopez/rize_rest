<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ActivityController extends Controller
{

    
    // public function index()
    // {
    //     $cards = Card::get();
    //     return $cards;
    // }

    // public function show($deck_id)
    // {
    //     return Card::where('deck_id', $deck_id)->get();
    // }

    // public function store(Request $request)
    // {
    //     $data = $request->validate([
    //         'deck_id' => "required",
    //         'designation' => "required",
    //         'name' => "required",
    //         'nb_in_deck' => "required|integer",
    //         'img_url' => "required",
    //     ]);

    //     return response()->json(Card::create($data));
    // }


    // public function update(Request $request, $id)
    // {

    //     Card::where('id', $id)->first();

    //     $data = $request->validate([
    //         'nb_in_deck' => "required|integer",
    //     ]);

    //     return response()->json(Card::where('id', $id)->update($data));
    // }

    // public function destroy($id)
    // {
    //     return response()->json(Card::where('id', $id)->delete());
    // }
}
