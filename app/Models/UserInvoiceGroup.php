<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInvoiceGroup extends Model
{
    use HasFactory;

    protected $table = 'user_invoice_standard_item_groups';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'invoice_standard_item_group_number',
        'invoice_standard_item_group_description',
        'is_folder'
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
