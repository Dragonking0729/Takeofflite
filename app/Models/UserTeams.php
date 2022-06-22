<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTeams extends Model
{
    use HasFactory;

    protected $table = 'user_teams';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'team_id',
        'user_id',
        'user_name',
        'is_master'
    ];
}
