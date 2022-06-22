<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectShare extends Model
{
    use HasFactory;

    protected $table = 'user_project_shares';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'share_sender_user_id',
        'share_receiver_user_id',
        'share_project_number',
        'allow_edit',
    ];
}
