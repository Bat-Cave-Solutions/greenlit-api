<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 */
class Organization extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all productions for this organization.
     */
    public function productions(): HasMany
    {
        return $this->hasMany(Production::class);
    }

    /**
     * Get all custom emission factors for this organization.
     */
    public function customEmissionFactors(): HasMany
    {
        return $this->hasMany(CustomEmissionFactor::class);
    }

    /**
     * Scope to filter by active organizations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
