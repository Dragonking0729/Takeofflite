<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProposalGroup extends Model
{
    use HasFactory;

    protected $table = 'user_proposal_standard_item_groups';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'proposal_standard_item_group_number',
        'proposal_standard_item_group_description',
        'is_folder'
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
