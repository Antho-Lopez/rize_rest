<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DayTraining extends Model
{
    use HasFactory, SoftDeletes;


    protected $fillable = [
        'day_id',
        'training_id'
    ];

    public $timestamps = false;

    protected $table = 'day_training';

    public function days()
    {
        return $this->hasMany(Day::class);
    }

    public function trainings()
    {
        return $this->hasMany(Training::class);
    }
}
