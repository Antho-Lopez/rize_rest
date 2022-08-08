<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exercice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'muscle_id',
    ];

    public function muscle()
    {
        return $this->belongsTo(Muscle::class);
    }

    public function repetitions()
    {
        return $this->hasMany(Repetition::class);
    }
}
