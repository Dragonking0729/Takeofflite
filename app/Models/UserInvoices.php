<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInvoices extends Model
{
    use HasFactory;

    protected $table = 'user_invoices';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'project_id',
        'invoice_name',
        'top_info_text',
        'bottom_info_text',
        'preview_content',
        'is_locked',
        'is_viewed'
    ];
}
