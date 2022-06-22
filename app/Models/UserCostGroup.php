<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCostGroup extends Model
{
    use HasFactory;

    protected $table = 'user_cost_groups';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'cost_group_number',
        'cost_group_desc',
        'is_folder'
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
