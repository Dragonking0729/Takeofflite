<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserQuestion extends Model
{
    use HasFactory;

    protected $table = 'user_questions';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'question',
        'notes',
        'type'
    ];
}
