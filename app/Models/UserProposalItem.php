<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProposalItem extends Model
{
    use HasFactory;

    protected $table = 'user_proposal_standard_items';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'proposal_standard_item_group_number',
        'proposal_standard_item_number',
        'proposal_standard_item_description',
        'proposal_standard_item_uom',
        'proposal_standard_item_default_markup_percent',
        'proposal_standard_item_explanatory_text',
        'proposal_standard_item_internal_notes',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
