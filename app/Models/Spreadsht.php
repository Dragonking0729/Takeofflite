<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Spreadsht extends Model
{
    use HasFactory;

    protected $table = 'user_spreadsht';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'project_id',
        'ss_item_cost_group_number',
        'ss_item_cost_group_desc',
        'ss_item_number',
        'ss_item_description',
        'ss_item_takeoff_uom',
        'ss_item_takeoff_quantity',
        'ss_labor_conversion_factor',
        'ss_labor_waste_percent',
        'ss_labor_order_quantity',
        'ss_labor_uom',
        'ss_labor_price',
        'ss_labor_total',
        'ss_labor_markup_percent',
        'ss_labor_markup_dollar_amount',
        'ss_labor_total_markedup_total',
        'ss_use_labor',
        'ss_material_conversion_factor',
        'ss_material_waste_percent',
        'ss_material_order_quantity',
        'ss_material_uom',
        'ss_material_price',
        'ss_material_total',
        'ss_material_markup_percent',
        'ss_material_markup_dollar_amount',
        'ss_material_total_markedup_total',
        'ss_use_material',
        'ss_subcontract_conversion_factor',
        'ss_subcontract_waste_percent',
        'ss_subcontract_order_quantity',
        'ss_subcontract_uom',
        'ss_subcontract_price',
        'ss_subcontract_total',
        'ss_subcontract_markup_percent',
        'ss_subcontract_markup_dollar_amount',
        'ss_subcontract_total_markedup_total',
        'ss_use_sub',
        'ss_other_conversion_factor',
        'ss_other_waste_percent',
        'ss_other_order_quantity',
        'ss_other_uom',
        'ss_other_price',
        'ss_other_total',
        'ss_other_markup_percent',
        'ss_other_markup_dollar_amount',
        'ss_other_total_markedup_total',
        'ss_home_depot_sku',
        'ss_home_depot_price',
        'ss_lowes_sku',
        'ss_lowes_price',
        'ss_whitecap_sku',
        'ss_whitecap_price',
        'ss_bls_number',
        'ss_bls_price',
        'ss_grainger_number',
        'ss_grainger_price',
        'ss_wcyw_number',
        'ss_wcyw_price',
        'ss_selected_vendor',
        'ss_quote_or_invoice_item',
        'ss_line_total',
        'ss_price_info',
        'ss_notes',
        'ss_location',
        'ss_is_qv'
    ];
}
