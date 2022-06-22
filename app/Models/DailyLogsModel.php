<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyLogsModel extends Model
{
    use HasFactory;

    protected $table = 'user_project_daily_logs';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'project_id',
        'log_entry_date',
        'customer_view',
        'note',
        'attached_files',
        'weather',
    ];
}
