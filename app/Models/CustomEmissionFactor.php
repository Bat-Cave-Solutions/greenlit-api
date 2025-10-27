<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 */
class CustomEmissionFactor extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'organization_id',
        'activity_code',
        'name',
        'description',
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
     * Get the organization that owns the custom factor.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get all emissions using this custom factor.
     */
    public function emissions(): HasMany
    {
        return $this->hasMany(Emission::class);
    }

    /**
     * Scope to filter by organization.
     */
    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Scope to filter by activity code.
     */
    public function scopeForActivity($query, $activityCode)
    {
        return $query->where('activity_code', $activityCode);
    }

    /**
     * Scope to filter by active factors.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
