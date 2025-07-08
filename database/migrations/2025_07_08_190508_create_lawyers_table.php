<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lawyers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('mobile')->nullable();
            $table->string('fax')->nullable();
            $table->string('license_number')->unique();
            $table->string('firm_name');
            $table->text('bio')->nullable();
            $table->string('profile_image')->nullable();
            $table->json('specializations'); // Array of specialization areas
            $table->json('languages')->nullable(); // Languages spoken
            $table->integer('years_experience')->default(0);
            
            // Address information
            $table->text('address');
            $table->string('city');
            $table->string('province');
            $table->string('postal_code');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Professional details
            $table->string('admission_date')->nullable(); // When admitted to practice
            $table->string('law_society')->default('Law Society of South Africa');
            $table->json('courts_admitted')->nullable(); // Courts they can practice in
            
            // Contact preferences
            $table->boolean('accepts_new_clients')->default(true);
            $table->json('consultation_methods')->nullable(); // in-person, video, phone
            $table->decimal('consultation_fee', 8, 2)->nullable();
            $table->string('website')->nullable();
            $table->json('social_media')->nullable(); // LinkedIn, etc.
            
            // Business hours
            $table->json('business_hours')->nullable();
            $table->text('notes')->nullable();
            
            // Status and verification
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('verified_at')->nullable();
            $table->string('verified_by')->nullable();
            
            // SEO and search
            $table->string('slug')->unique();
            $table->json('keywords')->nullable();
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->integer('review_count')->default(0);
            
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['city', 'province']);
            $table->index(['is_active', 'is_verified']);
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lawyers');
    }
};