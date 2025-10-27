<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Emission extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'production_id',
        'record_date',
        'record_period',
        'department',
        'activity_code',
        'scope',
        'country',
        'emission_factor_id',
        'custom_factor_id',
        'calculation_version',
        'calculated_co2e',
        'record_flags',
        'data',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'record_date' => 'date',
        'data' => 'array',
        'calculated_co2e' => 'decimal:6',
        'record_flags' => 'integer',
    ];

    /**
     * Get the production that this emission belongs to.
     */
    public function production(): BelongsTo
    {
        return $this->belongsTo(Production::class);
    }

    /**
     * Get the emission factor used for this emission.
     */
    public function emissionFactor(): BelongsTo
    {
        return $this->belongsTo(EmissionFactor::class);
    }

    /**
     * Get the custom emission factor used for this emission.
     */
    public function customEmissionFactor(): BelongsTo
    {
        return $this->belongsTo(CustomEmissionFactor::class, 'custom_factor_id');
    }

    /**
     * Scope to filter by production.
     */
    public function scopeForProduction($query, $productionId)
    {
        return $query->where('production_id', $productionId);
    }

    /**
     * Scope to filter by activity code.
     */
    public function scopeForActivity($query, $activityCode)
    {
        return $query->where('activity_code', $activityCode);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('record_date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by scope.
     */
    public function scopeForScope($query, $scope)
    {
        return $query->where('scope', $scope);
    }

    /**
     * Check if this emission has flight data.
     */
    public function hasFlightData(): bool
    {
        return str_starts_with($this->activity_code, 'flight_') &&
               isset($this->data['flight_origin'], $this->data['flight_destination']);
    }

    /**
     * Get the flight origin (uses generated column when available).
     */
    public function getFlightOriginAttribute(): ?string
    {
        return $this->attributes['flight_origin'] ?? $this->data['flight_origin'] ?? null;
    }

    /**
     * Get the flight destination (uses generated column when available).
     */
    public function getFlightDestinationAttribute(): ?string
    {
        return $this->attributes['flight_destination'] ?? $this->data['flight_destination'] ?? null;
    }

    /**
     * Get the flight distance in kilometers (uses generated column when available).
     */
    public function getFlightDistanceKmAttribute(): ?float
    {
        return $this->attributes['flight_distance_km'] ?? $this->data['flight_distance_km'] ?? null;
    }
}
