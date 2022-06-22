<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInvoiceItem extends Model
{
    use HasFactory;

    protected $table = 'user_invoice_standard_items';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'invoice_standard_item_group_number',
        'invoice_standard_item_number',
        'invoice_standard_item_description',
        'invoice_standard_item_uom',
        'invoice_standard_item_default_markup_percent',
        'invoice_standard_item_explanatory_text',
        'invoice_standard_item_internal_notes',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
