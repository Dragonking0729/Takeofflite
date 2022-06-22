<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAddons extends Model
{
    use HasFactory;

    protected $table = 'user_addons';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'addon_name',
        'addon_method',
        'addon_category',
        'addon_value',
    ];
}
