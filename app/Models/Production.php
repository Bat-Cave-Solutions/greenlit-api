<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 */
class Production extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'calculation_version',
        'is_active',
        'organization_id',
        'base_year',
        'reporting_period_start',
        'reporting_period_end',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'base_year' => 'date',
        'reporting_period_start' => 'date',
        'reporting_period_end' => 'date',
    ];

    /**
     * Get the organization that owns the production.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get all emissions for this production.
     */
    public function emissions(): HasMany
    {
        return $this->hasMany(Emission::class);
    }

    /**
     * Scope to filter by active productions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by organization.
     */
    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }
}
