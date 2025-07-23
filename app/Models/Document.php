<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'file_path',
        'file_name',
        'file_type',
        'file_extension',
        'file_size',
        'category',
        'tags',
        'download_count',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'download_count' => 'integer',
        'file_size' => 'integer',
        'sort_order' => 'integer',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $appends = [
        'formatted_file_size',
    ];

    // Automatically generate slug from name
    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($document) {
            if (empty($document->slug)) {
                $document->slug = Str::slug($document->name);
            }
        });
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByFileType($query, $fileType)
    {
        return $query->where('file_type', $fileType);
    }

    public function scopePopular($query)
    {
        return $query->orderBy('download_count', 'desc');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
        });
    }

    // Accessors
    public function getFormattedFileSizeAttribute()
    {
        $bytes = $this->file_size;
        
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    public function getTagsAttribute($value)
    {
        if (is_null($value)) {
            return [];
        }
        
        // If it's already an array, return it
        if (is_array($value)) {
            return $value;
        }
        
        // If it's a JSON string, decode it
        if (is_string($value) && $this->isJson($value)) {
            return json_decode($value, true) ?: [];
        }
        
        // If it's a comma-separated string, split it
        if (is_string($value)) {
            return array_filter(array_map('trim', explode(',', $value)));
        }
        
        return [];
    }

    public function setTagsAttribute($value)
    {
        if (is_null($value)) {
            $this->attributes['tags'] = null;
            return;
        }
        
        // If it's already an array, store as JSON
        if (is_array($value)) {
            $this->attributes['tags'] = json_encode($value);
            return;
        }
        
        // If it's a string, store as is (will be handled by accessor)
        $this->attributes['tags'] = $value;
    }

    private function isJson($string)
    {
        if (!is_string($string)) {
            return false;
        }
        
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public function getFileUrlAttribute()
    {
        return url('storage/' . $this->file_path);
    }

    // Helper methods
    public function incrementDownloadCount()
    {
        $this->increment('download_count');
    }

    public function isImage()
    {
        return in_array($this->file_extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    public function isPdf()
    {
        return $this->file_extension === 'pdf';
    }

    public function isDocument()
    {
        return in_array($this->file_extension, ['doc', 'docx', 'txt', 'rtf']);
    }
}
