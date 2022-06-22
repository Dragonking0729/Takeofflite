<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCostItem extends Model
{
    use HasFactory;

    protected $table = 'user_cost_items';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'cost_group_number',
        'item_number',
        'item_desc',
        'takeoff_uom',
        'labor_conversion_factor',
        'labor_uom',
        'labor_price',
        'use_labor',
        'material_conversion_factor',
        'material_uom',
        'material_price',
        'use_material',
        'subcontract_conversion_factor',
        'subcontract_uom',
        'subcontract_price',
        'use_sub    ',
        'other_conversion_factor',
        'other_uom',
        'other_price',
        'home_depot_sku',
        'home_depot_price',
        'lowes_sku',
        'lowes_price',
        'whitecap_sku',
        'whitecap_price',
        'bls_number',
        'bls_price',
        'wcyw_number',
        'wcyw_price',
        'grainger_number',
        'grainger_price',
        'takeoff_cal',
        'invoice_item_default',
        'selected_vendor',
        'formula_params',
        'notes',
        'labor_add_on_percentage',
        'material_add_on_percentage',
        'subcontract_add_on_percentage',
        'material_waste_factor',
        'labor_conversion_toggle_status',
        'material_conversion_toggle_status',
        'subcontract_conversion_toggle_status',
        'quote_or_invoice_item',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
