<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiRepository extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'features',
        'pricing',
        'documentation_quality',
        'community_rating',
        'description',
        'website_url',
        'documentation_url',
        'tags',
        'is_active',
    ];

    protected $casts = [
        'features' => 'array',
        'tags' => 'array',
        'is_active' => 'boolean',
        'documentation_quality' => 'decimal:1',
        'community_rating' => 'decimal:1',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByPricing($query, $pricing)
    {
        return $query->where('pricing', $pricing);
    }
}