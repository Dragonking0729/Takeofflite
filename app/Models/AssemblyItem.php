<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssemblyItem extends Model
{
    use HasFactory;

    protected $table = 'user_assembly_item';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'assembly_number',
        'item_cost_group_number',
        'item_number',
        'formula_params',
        'item_order'
    ];
}
