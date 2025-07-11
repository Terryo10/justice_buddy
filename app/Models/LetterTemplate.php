<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LetterTemplate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'template_content',
        'required_fields',
        'optional_fields',
        'category',
        'ai_instructions',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'required_fields' => 'array',
        'optional_fields' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            if (empty($template->slug)) {
                $template->slug = Str::slug($template->name);
            }
        });

        static::updating(function ($template) {
            if ($template->isDirty('name') && empty($template->slug)) {
                $template->slug = Str::slug($template->name);
            }
        });
    }

    public function letterRequests(): HasMany
    {
        return $this->hasMany(LetterRequest::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get all field names (required + optional)
     */
    public function getAllFields(): array
    {
        return array_merge(
            $this->required_fields ?? [],
            $this->optional_fields ?? []
        );
    }

    /**
     * Validate client matters against template requirements
     */
    public function validateClientMatters(array $clientMatters): array
    {
        $errors = [];
        $requiredFields = $this->required_fields ?? [];

        foreach ($requiredFields as $field) {
            if (!isset($clientMatters[$field]) || empty($clientMatters[$field])) {
                $errors[] = "Required field '{$field}' is missing or empty";
            }
        }

        return $errors;
    }

    /**
     * Get available categories
     */
    public static function getAvailableCategories(): array
    {
        return [
            'eviction' => 'Eviction Letters',
            'employment' => 'Employment Letters', 
            'family' => 'Family Law Letters',
            'consumer' => 'Consumer Rights Letters',
            'criminal' => 'Criminal Law Letters',
            'property' => 'Property Law Letters',
            'debt' => 'Debt & Credit Letters',
            'general' => 'General Legal Letters',
        ];
    }
}