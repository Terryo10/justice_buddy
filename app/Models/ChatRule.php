<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatRule extends Model
{
    protected $fillable = [
        'name',
        'rule_text',
        'type',
        'priority',
        'is_active',
        'model_name',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByModel($query, $modelName)
    {
        return $query->where(function ($q) use ($modelName) {
            $q->where('model_name', $modelName)
              ->orWhereNull('model_name');
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('priority', 'desc')
                    ->orderBy('created_at', 'asc');
    }

    public function scopeForChat($query, $modelName = null)
    {
        $query = $query->active()->ordered();
        
        if ($modelName) {
            $query = $query->byModel($modelName);
        }
        
        return $query;
    }
}
