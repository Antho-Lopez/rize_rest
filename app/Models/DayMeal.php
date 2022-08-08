<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DayMeal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'day_id',
        'meal_id'
    ];

    public $timestamps = false;

    protected $table = 'day_meal';

    public function days()
    {
        return $this->hasMany(Day::class);
    }

    public function meals()
    {
        return $this->hasMany(Meal::class);
    }
}
