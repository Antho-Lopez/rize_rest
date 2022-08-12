<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Training extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    public function four_last_sessions()
    {
        return $this->hasMany(Session::class)->orderBy('session_day', 'DESC')->take(4);
    }

    public function muscles()
    {
        return $this->hasMany(Muscle::class);
    }

    public function days()
    {
        return $this->belongsToMany(Day::class);
    }


}
