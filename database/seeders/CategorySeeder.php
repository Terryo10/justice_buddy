<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Evictions',
                'slug' => 'evictions',
                'description' => 'Legal information and assistance regarding eviction notices, tenant rights, and landlord disputes.',
                'is_active' => true,
            ],
            [
                'name' => 'Employment Law',
                'slug' => 'employment-law',
                'description' => 'Workplace rights, labor disputes, and employment-related legal matters.',
                'is_active' => true,
            ],
            [
                'name' => 'Family Law',
                'slug' => 'family-law',
                'description' => 'Divorce, custody, maintenance, and other family-related legal issues.',
                'is_active' => true,
            ],
            [
                'name' => 'Consumer Rights',
                'slug' => 'consumer-rights',
                'description' => 'Protection for consumers, product liability, and service disputes.',
                'is_active' => true,
            ],
            [
                'name' => 'Criminal Law',
                'slug' => 'criminal-law',
                'description' => 'Criminal defense, bail applications, and criminal procedure information.',
                'is_active' => true,
            ],
            [
                'name' => 'Property Law',
                'slug' => 'property-law',
                'description' => 'Property transactions, disputes, and real estate legal matters.',
                'is_active' => true,
            ],
            [
                'name' => 'Debt & Credit',
                'slug' => 'debt-credit',
                'description' => 'Debt counseling, credit disputes, and financial legal assistance.',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}