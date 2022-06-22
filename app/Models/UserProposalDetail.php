<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProposalDetail extends Model
{
    use HasFactory;

    protected $table = 'user_proposal_detail';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'project_id',
        'proposal_id',
        'proposal_item_group_number',
        'proposal_item_group_desc',
        'proposal_item_number',
        'proposal_item_description',
        'proposal_item_uom',
        'proposal_item_billing_quantity',
        'proposal_unit_price',
        'proposal_item_markup_percent',
        'proposal_markup_dollars',
        'proposal_selected_vendor',
        'proposal_contractor_cost_total',
        'proposal_customer_price_per_unit',
        'proposal_customer_price',
        'proposal_customer_scope_explanation',
        'proposal_internal_notes',
        'proposal_list_order',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
