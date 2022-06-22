<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assembly extends Model
{
    use HasFactory;

    protected $table = 'user_assembly';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'assembly_number',
        'assembly_desc',
        'is_folder',
        'is_qv',
        'interview_notes'
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
