<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SheetObject extends Model
{
    use HasFactory;

    protected $table = 'user_sheet_object';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'sheet_id',
        'user_id',
        'measure_name',
        'object_id',
        'is_measurement',
        'color',
        'perimeter',
        'area',
        'info'
    ];
}
