<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'activity_id',
        'email',
        'sex',
        'age',
        'height',
        'current_weight',
        'goal_weight',
        'email_verified_at',
        'password',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function meals()
    {
        return $this->hasMany(Meal::class);
    }

    public function trainings()
    {
        return $this->hasMany(Training::class);
    }

    public function sleeps()
    {
        return $this->hasMany(Sleep::class);
    }

    public function old_weight()
    {
        return $this->hasMany(OldWeight::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
