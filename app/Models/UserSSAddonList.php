<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSSAddonList extends Model
{
    use HasFactory;

    protected $table = 'user_ss_add_on_list';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'project_id',
        'ss_add_on_name',
        'addon_value',
        'ss_add_on_value',
        'addon_category',
        'addon_method'
    ];
}
