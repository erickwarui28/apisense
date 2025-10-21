<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedRecommendation extends Model
{
    protected $fillable = [
        'user_id',
        'project_description',
        'requirements_summary',
        'requirements_data',
        'recommendations_data',
        'source_type',
        'original_filename',
    ];

    protected $casts = [
        'requirements_data' => 'array',
        'recommendations_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the saved recommendation
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
