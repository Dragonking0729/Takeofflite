<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlipModel extends Model
{
    use HasFactory;

    protected $table = 'user_project_flip_analysis';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'project_id',
        'purchase_price',
        'arv',
        'repair_cost',
        'acquisition_costs',
        'detailed_acquisition_costs',
        'holding_costs',
        'detailed_holding_costs',
        'selling_costs',
        'detailed_selling_costs',
        'financing_costs',
        'detailed_financing_costs'
    ];
}
