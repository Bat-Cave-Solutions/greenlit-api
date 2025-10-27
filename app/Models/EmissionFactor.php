<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 */
class EmissionFactor extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'activity_code',
        'name',
        'description',
        'source',
        'country',
        'year',
        'unit',
        'co2_factor',
        'ch4_factor',
        'n2o_factor',
        'co2e_factor',
        'metadata',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
        'co2_factor' => 'decimal:6',
        'ch4_factor' => 'decimal:6',
        'n2o_factor' => 'decimal:6',
        'co2e_factor' => 'decimal:6',
    ];

    /**
     * Get all emissions using this factor.
     */
    public function emissions(): HasMany
    {
        return $this->hasMany(Emission::class);
    }

    /**
     * Scope to filter by activity code.
     */
    public function scopeForActivity($query, $activityCode)
    {
        return $query->where('activity_code', $activityCode);
    }

    /**
     * Scope to filter by country.
     */
    public function scopeForCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    /**
     * Scope to filter by year.
     */
    public function scopeForYear($query, $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Scope to filter by active factors.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get latest factor for activity and country.
     */
    public function scopeLatest($query, $activityCode, $country)
    {
        return $query->forActivity($activityCode)
            ->forCountry($country)
            ->active()
            ->orderBy('year', 'desc')
            ->limit(1);
    }
}
