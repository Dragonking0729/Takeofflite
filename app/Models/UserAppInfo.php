<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAppInfo extends Model
{
    use HasFactory;

    protected $table = 'user_app_info';

    public $timestamps = false;

    protected $fillable = [
        'email',
        'phone',
        'ext',
        'app_version',
        'copy_right_text',
        'eula_url',
        'policy_url',
        'support_text',
        'support_link_text',
        'support_link',
    ];
}
