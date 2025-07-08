<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Lawyer extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'mobile',
        'fax',
        'license_number',
        'firm_name',
        'bio',
        'profile_image',
        'specializations',
        'languages',
        'years_experience',
        'address',
        'city',
        'province',
        'postal_code',
        'latitude',
        'longitude',
        'admission_date',
        'law_society',
        'courts_admitted',
        'accepts_new_clients',
        'consultation_methods',
        'consultation_fee',
        'website',
        'social_media',
        'business_hours',
        'notes',
        'is_verified',
        'is_active',
        'verified_at',
        'verified_by',
        'slug',
        'keywords',
        'rating',
        'review_count',
    ];

    protected $casts = [
        'specializations' => 'array',
        'languages' => 'array',
        'courts_admitted' => 'array',
        'consultation_methods' => 'array',
        'social_media' => 'array',
        'business_hours' => 'array',
        'keywords' => 'array',
        'accepts_new_clients' => 'boolean',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'verified_at' => 'datetime',
        'consultation_fee' => 'decimal:2',
        'rating' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'years_experience' => 'integer',
        'review_count' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($lawyer) {
            if (empty($lawyer->slug)) {
                $lawyer->slug = Str::slug($lawyer->first_name . ' ' . $lawyer->last_name . ' ' . $lawyer->firm_name);
            }
        });

        static::updating(function ($lawyer) {
            if ($lawyer->isDirty(['first_name', 'last_name', 'firm_name']) && empty($lawyer->slug)) {
                $lawyer->slug = Str::slug($lawyer->first_name . ' ' . $lawyer->last_name . ' ' . $lawyer->firm_name);
            }
        });
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getFormattedAddressAttribute(): string
    {
        return $this->address . ', ' . $this->city . ', ' . $this->province . ' ' . $this->postal_code;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeAcceptingClients($query)
    {
        return $query->where('accepts_new_clients', true);
    }

    public function scopeBySpecialization($query, $specialization)
    {
        return $query->whereJsonContains('specializations', $specialization);
    }

    public function scopeByCity($query, $city)
    {
        return $query->where('city', 'like', "%{$city}%");
    }

    public function scopeByProvince($query, $province)
    {
        return $query->where('province', 'like', "%{$province}%");
    }

    public function scopeNearLocation($query, $latitude, $longitude, $radiusKm = 50)
    {
        $haversine = "(6371 * acos(cos(radians({$latitude})) 
                     * cos(radians(latitude)) 
                     * cos(radians(longitude) - radians({$longitude})) 
                     + sin(radians({$latitude})) 
                     * sin(radians(latitude))))";
        
        return $query->selectRaw("*, {$haversine} AS distance")
                    ->whereRaw("{$haversine} < ?", [$radiusKm])
                    ->orderBy('distance');
    }

    // Helper methods
    public function hasSpecialization(string $specialization): bool
    {
        return in_array($specialization, $this->specializations ?? []);
    }

    public function speaksLanguage(string $language): bool
    {
        return in_array($language, $this->languages ?? []);
    }

    // Available options for form selects
    public static function getSpecializationOptions(): array
    {
        return [
            'Criminal Law',
            'Family Law',
            'Property Law',
            'Employment Law',
            'Consumer Rights',
            'Debt & Credit',
            'Personal Injury',
            'Commercial Law',
            'Constitutional Law',
            'Immigration Law',
            'Tax Law',
            'Intellectual Property',
            'Environmental Law',
            'Labour Relations',
            'Administrative Law',
            'Medical Malpractice',
            'Insurance Law',
            'Banking Law',
            'Competition Law',
            'Human Rights',
        ];
    }

    public static function getLanguageOptions(): array
    {
        return [
            'English',
            'Afrikaans',
            'Zulu',
            'Xhosa',
            'Sotho',
            'Tswana',
            'Pedi',
            'Venda',
            'Tsonga',
            'Swati',
            'Ndebele',
        ];
    }

    public static function getProvinceOptions(): array
    {
        return [
            'Gauteng',
            'Western Cape',
            'Eastern Cape',
            'KwaZulu-Natal',
            'Free State',
            'Limpopo',
            'Mpumalanga',
            'North West',
            'Northern Cape',
        ];
    }

    public static function getConsultationMethodOptions(): array
    {
        return [
            'In-person',
            'Video call',
            'Phone call',
            'Email consultation',
        ];
    }

    public static function getCourtsOptions(): array
    {
        return [
            'Magistrates Court',
            'High Court',
            'Supreme Court of Appeal',
            'Constitutional Court',
            'Labour Court',
            'Labour Appeal Court',
            'Land Claims Court',
            'Competition Appeal Court',
        ];
    }
}