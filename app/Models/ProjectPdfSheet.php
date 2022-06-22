<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectPdfSheet extends Model
{
    use HasFactory;

    protected $table = 'user_project_pdf_sheet';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'project_id',
        'sheet_name',
        'pdf_path',
        'file',
        'sheet_order',
        'feet',
        'inch',
        'scale',
        'zoom',
        'x',
        'y',
        'width',
        'height',
        'category'
    ];
}
