<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'country',
        'region',
        'provider_name',
        'provider_contact',
        'description',
        'price',
        'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function scopeByCountry($query, $country)
    {
        return $query->where('country', 'like', '%' . $country . '%');
    }

    public function scopeByRegion($query, $region)
    {
        return $query->where('region', 'like', '%' . $region . '%');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', 'like', '%' . $type . '%');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
