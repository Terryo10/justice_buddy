<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Document;
use Illuminate\Support\Str;

class DocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documents = [
            [
                'name' => 'Affidavit of Support Template',
                'description' => 'A comprehensive template for creating affidavits of support for immigration and legal purposes.',
                'file_path' => 'documents/affidavit-of-support-template.pdf',
                'file_name' => 'affidavit-of-support-template.pdf',
                'file_type' => 'application/pdf',
                'file_extension' => 'pdf',
                'file_size' => 256000,
                'category' => 'Affidavits',
                'tags' => ['affidavit', 'support', 'immigration', 'template'],
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'General Affidavit Form',
                'description' => 'A standard general affidavit form for various legal declarations and statements.',
                'file_path' => 'documents/general-affidavit-form.pdf',
                'file_name' => 'general-affidavit-form.pdf',
                'file_type' => 'application/pdf',
                'file_extension' => 'pdf',
                'file_size' => 180000,
                'category' => 'Affidavits',
                'tags' => ['affidavit', 'general', 'form', 'legal'],
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'Power of Attorney Document',
                'description' => 'Legal document granting authority to act on behalf of another person in legal matters.',
                'file_path' => 'documents/power-of-attorney.docx',
                'file_name' => 'power-of-attorney.docx',
                'file_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'file_extension' => 'docx',
                'file_size' => 45000,
                'category' => 'Legal Documents',
                'tags' => ['power-of-attorney', 'legal', 'authorization'],
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Contract Agreement Template',
                'description' => 'A basic contract agreement template for business and personal use.',
                'file_path' => 'documents/contract-agreement-template.docx',
                'file_name' => 'contract-agreement-template.docx',
                'file_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'file_extension' => 'docx',
                'file_size' => 52000,
                'category' => 'Contracts',
                'tags' => ['contract', 'agreement', 'template', 'business'],
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 4,
            ],
            [
                'name' => 'Lease Agreement Form',
                'description' => 'Standard residential lease agreement form with terms and conditions.',
                'file_path' => 'documents/lease-agreement-form.pdf',
                'file_name' => 'lease-agreement-form.pdf',
                'file_type' => 'application/pdf',
                'file_extension' => 'pdf',
                'file_size' => 320000,
                'category' => 'Contracts',
                'tags' => ['lease', 'rental', 'agreement', 'property'],
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Non-Disclosure Agreement',
                'description' => 'Confidentiality agreement to protect sensitive information and trade secrets.',
                'file_path' => 'documents/non-disclosure-agreement.pdf',
                'file_name' => 'non-disclosure-agreement.pdf',
                'file_type' => 'application/pdf',
                'file_extension' => 'pdf',
                'file_size' => 120000,
                'category' => 'Contracts',
                'tags' => ['nda', 'confidentiality', 'non-disclosure', 'business'],
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 6,
            ],
            [
                'name' => 'Last Will and Testament',
                'description' => 'Legal document for expressing wishes regarding the distribution of property after death.',
                'file_path' => 'documents/last-will-testament.docx',
                'file_name' => 'last-will-testament.docx',
                'file_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'file_extension' => 'docx',
                'file_size' => 68000,
                'category' => 'Wills & Estates',
                'tags' => ['will', 'testament', 'estate', 'inheritance'],
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Small Claims Court Filing Guide',
                'description' => 'Step-by-step guide for filing small claims court cases and required procedures.',
                'file_path' => 'documents/small-claims-court-guide.pdf',
                'file_name' => 'small-claims-court-guide.pdf',
                'file_type' => 'application/pdf',
                'file_extension' => 'pdf',
                'file_size' => 450000,
                'category' => 'Court Forms',
                'tags' => ['small-claims', 'court', 'filing', 'guide'],
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 8,
            ],
        ];

        foreach ($documents as $documentData) {
            // Generate slug from name
            $documentData['slug'] = Str::slug($documentData['name']);
            
            // Add some random download counts for testing
            $documentData['download_count'] = rand(0, 100);
            
            Document::create($documentData);
        }
    }
}
