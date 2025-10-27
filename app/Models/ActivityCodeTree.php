<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class ActivityCodeTree extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'activity_code_tree';

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'code';

    /**
     * Indicates if the model's ID is auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The data type of the primary key ID.
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'parent_code',
        'level',
        'scope',
        'unit',
        'is_leaf',
        'is_active',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_leaf' => 'boolean',
        'is_active' => 'boolean',
        'level' => 'integer',
        'scope' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Get the parent activity code.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ActivityCodeTree::class, 'parent_code', 'code');
    }

    /**
     * Get all child activity codes.
     */
    public function children(): HasMany
    {
        return $this->hasMany(ActivityCodeTree::class, 'parent_code', 'code')
            ->orderBy('sort_order');
    }

    /**
     * Scope to filter by scope.
     */
    public function scopeForScope($query, $scope)
    {
        return $query->where('scope', $scope);
    }

    /**
     * Scope to filter by active codes.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by leaf nodes (codes that can have emissions).
     */
    public function scopeLeaf($query)
    {
        return $query->where('is_leaf', true);
    }

    /**
     * Scope to get root level codes.
     */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_code')->orderBy('sort_order');
    }

    /**
     * Get all descendants of this code.
     */
    /**
     * @return Collection<int, ActivityCodeTree>
     */
    public function descendants(): Collection
    {
        $descendants = collect();
        /** @var Collection<int, ActivityCodeTree> $children */
        $children = $this->children()->get();

        foreach ($children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->descendants());
        }

        return $descendants;
    }

    /**
     * Get full hierarchy path.
     */
    public function getHierarchyPath(): string
    {
        $path = [];
        $current = $this;

        while ($current) {
            array_unshift($path, $current->name);
            $current = $current->parent;
        }

        return implode(' > ', $path);
    }
}
