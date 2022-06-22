<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPreferences extends Model
{
    use HasFactory;

    protected $table = 'user_preferences';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'quick_pick_share_user_id_1',
        'quick_pick_share_user_name_1',
        'quick_pick_share_user_id_2',
        'quick_pick_share_user_name_2',
        'quick_pick_share_user_id_3',
        'quick_pick_share_user_name_3',
        'quick_pick_share_user_id_4',
        'quick_pick_share_user_name_4',
        'quick_pick_share_user_id_5',
        'quick_pick_share_user_name_5',
        'quick_pick_share_user_id_6',
        'quick_pick_share_user_name_6',
        'minimum_acceptable_profit_dollars',
        'minimum_acceptable_profit_percent',
        'use_dollars_or_percent',
        'ss_cost_group_column_display',
        'ss_cost_item_column_display',
    ];
}
