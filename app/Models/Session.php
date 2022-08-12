<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Session extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'session_day',
        'training_id',
    ];

    public function training()
    {
        return $this->belongsTo(Training::class);
    }
    public function repetitions()
    {
        return $this->hasMany(Repetition::class);
    }
}
