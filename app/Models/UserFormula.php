<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFormula extends Model
{
    use HasFactory;

    protected $table = 'user_formulas';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'calculation_name',
        'formula_body',
        'user_id',
    ];
}
