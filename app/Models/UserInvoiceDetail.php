<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInvoiceDetail extends Model
{
    use HasFactory;

    protected $table = 'user_invoice_detail';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'project_id',
        'invoice_id',
        'invoice_item_group_number',
        'invoice_item_group_desc',
        'invoice_item_number',
        'invoice_item_description',
        'invoice_item_uom',
        'invoice_item_billing_quantity',
        'invoice_unit_price',
        'invoice_item_markup_percent',
        'invoice_markup_dollars',
        'invoice_selected_vendor',
        'invoice_contractor_cost_total',
        'invoice_customer_price_per_unit',
        'invoice_customer_price',
        'invoice_customer_scope_explanation',
        'invoice_internal_notes',
        'invoice_list_order',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
