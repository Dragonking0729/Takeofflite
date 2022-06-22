<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Uom extends Model
{
    use HasFactory;

    protected $table = 'uom';

//    protected $primaryKey = 'uom_name';

    public $timestamps = false;

    protected $fillable = [
        'uom_name'
    ];
}
