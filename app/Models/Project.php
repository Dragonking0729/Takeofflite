<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $table = 'user_projects';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'project_name',
        'street_address_1',
        'street_address_2',
        'city',
        'state',
        'postal_code',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address_1',
        'customer_address_2',
        'customer_city',
        'customer_state',
        'customer_postal_code',
        'profit_threshold_dollar',
        'profit_threshold_percent_of_arv',
        'interest_rate_percent',
        'holding_period_months',
        'property_taxes_percent',
        'square_footage',
        'geo_location',
        'minimum_acceptable_profit_dollars',
        'minimum_acceptable_profit_percent',
        'use_atom_api',
        'acreage',
        'lot_sf',
        'pool',
        'occupied',
        'property_class',
        'year_built',
        'heat_fuel_type',
        'heated_sf',
        'total_sf',
        'full_baths',
        'total_baths',
        'bath_fixtures',
        'bedrooms',
        'total_rooms',
        'basement_sf',
        'basement_finish',
        'property_condition',
        'exterior',
        'parking_sf_available',
        'parking_spaces',
        'architectural_style',
        'levels_stories',
        'year_built_last_modified',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
