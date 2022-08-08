<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Muscle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'training_id',
    ];

    public function training()
    {
        return $this->belongsTo(Training::class);
    }
}
