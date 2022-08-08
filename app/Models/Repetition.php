<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Repetition extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'exercice_id',
        'session_id',
        'nb_repetitions',
        'kilos',
    ];

    public function exercice()
    {
        return $this->belongsTo(Exercice::class);
    }

    public function session()
    {
        return $this->belongsTo(Session::class);
    }
}
