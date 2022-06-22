<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'user_email',
        'user_pass',
        'first_name',
        'last_name',
        'display_name',
        'user_status', // 0-trial period, 1-active subscription, 2-trial expired, 3-active but cancelling at end of this billing period
        'user_role',
        'company_name',
        'company_logo',
        'company_url',
        'street_address1',
        'street_address2',
        'city',
        'state',
        'postal_code',
        'phone',
        'stripe_id', // stripe customer id
        'subscription_id',
        'plan',
        'has_trial',
        'billing_end_date',
        'remember_token',
        'app_only',
        'email_verified_at',
        'created_at',
        'updated_at',
    ];

    public function project()
    {
        return $this->hasMany(\App\Models\Project::class);
    }
}
