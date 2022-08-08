<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sleep extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'day_id',
        'user_id',
        'go_to_sleep',
        'waking_up',
    ];

    public function day()
    {
        return $this->belongsTo(Day::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
