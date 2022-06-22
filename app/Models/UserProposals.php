<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProposals extends Model
{
    use HasFactory;

    protected $table = 'user_proposals';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'project_id',
        'proposal_name',
        'top_info_text',
        'bottom_info_text',
        'preview_content',
        'approve_status',
        'approve_name',
        'approve_date',
        'is_locked',
        'is_viewed'
    ];
}
