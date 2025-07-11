<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class LetterRequest extends Model
{
    protected $fillable = [
        'letter_template_id',
        'client_name',
        'client_email',
        'client_phone',
        'client_matters',
        'generated_letter',
        'status',
        'error_message',
        'document_path',
        'request_id',
        'generated_at',
        'device_id',
    ];

    protected $casts = [
        'client_matters' => 'array',
        'generated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($request) {
            if (empty($request->request_id)) {
                $request->request_id = 'LR-' . strtoupper(Str::random(8)) . '-' . now()->format('Ymd');
            }
        });
    }

    public function letterTemplate(): BelongsTo
    {
        return $this->belongsTo(LetterTemplate::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Mark request as processing
     */
    public function markAsProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }

    /**
     * Mark request as completed
     */
    public function markAsCompleted(string $generatedLetter, ?string $documentPath = null): void
    {
        $this->update([
            'status' => 'completed',
            'generated_letter' => $generatedLetter,
            'document_path' => $documentPath,
            'generated_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Mark request as failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Get status badge color for Filament
     */
    public function getStatusColor(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'processing' => 'info',
            'completed' => 'success',
            'failed' => 'danger',
            default => 'gray',
        };
    }
}