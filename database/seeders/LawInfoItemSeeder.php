<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\LawInfoItem;
use Illuminate\Database\Seeder;

class LawInfoItemSeeder extends Seeder
{
    public function run(): void
    {
        // Get category IDs
        $evictionsCategory = Category::where('slug', 'evictions')->first();
        $employmentCategory = Category::where('slug', 'employment-law')->first();
        $familyCategory = Category::where('slug', 'family-law')->first();
        $consumerCategory = Category::where('slug', 'consumer-rights')->first();
        $criminalCategory = Category::where('slug', 'criminal-law')->first();
        $propertyCategory = Category::where('slug', 'property-law')->first();
        $debtCategory = Category::where('slug', 'debt-credit')->first();

        $lawInfoItems = [
            // Evictions
            [
                'name' => 'Eviction Notice Help',
                'description' => 'Get assistance with understanding eviction notices and your rights as a tenant',
                'more_description' => 'This comprehensive guide helps tenants understand the legal process of eviction in South Africa. Learn about proper notice periods, your rights during the eviction process, and how to respond to eviction notices. We cover the different types of eviction notices, when they are valid, and what steps you can take to protect yourself.',
                'image' => 'https://storage.googleapis.com/glide-staging.appspot.com/uploads-v2/h94gPrzNSt8glmCWY1yb/pub/eviction-notice.png',
                'category_id' => $evictionsCategory->id,
                'sort_order' => 1,
            ],
            [
                'name' => 'Unlawful Eviction Protection',
                'description' => 'Learn about protection against illegal evictions and emergency court orders',
                'more_description' => 'Understanding your protection against unlawful evictions is crucial for tenants. This guide explains what constitutes an unlawful eviction, how to obtain emergency court orders, and the legal remedies available to you. We also cover the Prevention of Illegal Eviction Act and how it protects you.',
                'image' => 'https://storage.googleapis.com/glide-staging.appspot.com/uploads-v2/h94gPrzNSt8glmCWY1yb/pub/unlawful-eviction.png',
                'category_id' => $evictionsCategory->id,
                'sort_order' => 2,
            ],
            [
                'name' => 'Tenant Rights Guide',
                'description' => 'Comprehensive guide to your rights as a tenant in South Africa',
                'more_description' => 'A complete overview of tenant rights in South Africa, including rental agreements, maintenance responsibilities, privacy rights, and protection against discrimination. Learn about your rights regarding rent increases, property inspections, and dispute resolution.',
                'image' => 'https://storage.googleapis.com/glide-staging.appspot.com/uploads-v2/h94gPrzNSt8glmCWY1yb/pub/tenant-rights.png',
                'category_id' => $evictionsCategory->id,
                'sort_order' => 3,
            ],
            [
                'name' => 'Landlord Dispute Resolution',
                'description' => 'Assistance with resolving disputes with landlords through proper channels',
                'more_description' => 'When disputes arise with landlords, it\'s important to know the proper channels for resolution. This guide covers mediation services, rental tribunals, and legal action options. Learn how to document issues, communicate effectively, and seek fair resolution.',
                'image' => 'https://storage.googleapis.com/glide-staging.appspot.com/uploads-v2/h94gPrzNSt8glmCWY1yb/pub/landlord-dispute.png',
                'category_id' => $evictionsCategory->id,
                'sort_order' => 4,
            ],

            // Employment Law
            [
                'name' => 'Unfair Dismissal Claims',
                'description' => 'Guide to understanding and claiming unfair dismissal in the workplace',
                'more_description' => 'If you believe you have been unfairly dismissed, this guide will help you understand your rights and the process for claiming unfair dismissal. Learn about the Labour Relations Act, CCMA procedures, and what constitutes fair vs unfair dismissal.',
                'image' => 'https://storage.googleapis.com/glide-staging.appspot.com/uploads-v2/h94gPrzNSt8glmCWY1yb/pub/unfair-dismissal.png',
                'category_id' => $employmentCategory->id,
                'sort_order' => 1,
            ],
            [
                'name' => 'Workplace Discrimination',
                'description' => 'Understanding and addressing workplace discrimination and harassment',
                'more_description' => 'Workplace discrimination is illegal in South Africa. This guide explains the Employment Equity Act, types of discrimination, and how to file complaints. Learn about your rights regarding equal pay, promotion opportunities, and creating a discrimination-free workplace.',
                'image' => 'https://storage.googleapis.com/glide-staging.appspot.com/uploads-v2/h94gPrzNSt8glmCWY1yb/pub/workplace-discrimination.png',
                'category_id' => $employmentCategory->id,
                'sort_order' => 2,
            ],

            // Family Law
            [
                'name' => 'Divorce Proceedings',
                'description' => 'Step-by-step guide to divorce proceedings in South Africa',
                'more_description' => 'Navigate the divorce process with this comprehensive guide covering contested and uncontested divorces, division of assets, maintenance agreements, and child custody arrangements. Learn about the required documents and court procedures.',
                'image' => 'https://storage.googleapis.com/glide-staging.appspot.com/uploads-v2/h94gPrzNSt8glmCWY1yb/pub/divorce-proceedings.png',
                'category_id' => $familyCategory->id,
                'sort_order' => 1,
            ],
            [
                'name' => 'Child Custody Rights',
                'description' => 'Understanding parental rights and child custody arrangements',
                'more_description' => 'Child custody can be complex. This guide explains the best interests of the child principle, different types of custody arrangements, and how to modify custody orders. Learn about your rights and responsibilities as a parent.',
                'image' => 'https://storage.googleapis.com/glide-staging.appspot.com/uploads-v2/h94gPrzNSt8glmCWY1yb/pub/child-custody.png',
                'category_id' => $familyCategory->id,
                'sort_order' => 2,
            ],

            // Consumer Rights
            [
                'name' => 'Consumer Protection Act',
                'description' => 'Your rights under the Consumer Protection Act',
                'more_description' => 'The Consumer Protection Act provides important rights for consumers in South Africa. Learn about your right to fair dealing, product safety, warranty claims, and how to lodge complaints with the National Consumer Commission.',
                'image' => 'https://storage.googleapis.com/glide-staging.appspot.com/uploads-v2/h94gPrzNSt8glmCWY1yb/pub/consumer-protection.png',
                'category_id' => $consumerCategory->id,
                'sort_order' => 1,
            ],

            // Criminal Law
            [
                'name' => 'Bail Applications',
                'description' => 'Understanding bail applications and your rights when arrested',
                'more_description' => 'If you or someone you know has been arrested, understanding bail applications is crucial. This guide explains the bail process, factors considered by courts, and how to prepare for bail hearings. Learn about your constitutional rights during arrest and detention.',
                'image' => 'https://storage.googleapis.com/glide-staging.appspot.com/uploads-v2/h94gPrzNSt8glmCWY1yb/pub/bail-applications.png',
                'category_id' => $criminalCategory->id,
                'sort_order' => 1,
            ],

            // Property Law
            [
                'name' => 'Property Transfer Process',
                'description' => 'Guide to transferring property ownership in South Africa',
                'more_description' => 'The property transfer process can be complex. This guide explains the role of conveyancers, transfer duties, bond registration, and all the steps involved in transferring property ownership. Learn about timelines and costs involved.',
                'image' => 'https://storage.googleapis.com/glide-staging.appspot.com/uploads-v2/h94gPrzNSt8glmCWY1yb/pub/property-transfer.png',
                'category_id' => $propertyCategory->id,
                'sort_order' => 1,
            ],

            // Debt & Credit
            [
                'name' => 'Debt Review Process',
                'description' => 'Understanding debt review and debt counselling options',
                'more_description' => 'If you\'re struggling with debt, debt review might be an option. This guide explains the debt review process under the National Credit Act, eligibility requirements, and how it can help you manage your debt while protecting your assets.',
                'image' => 'https://storage.googleapis.com/glide-staging.appspot.com/uploads-v2/h94gPrzNSt8glmCWY1yb/pub/debt-review.png',
                'category_id' => $debtCategory->id,
                'sort_order' => 1,
            ],
        ];

        foreach ($lawInfoItems as $item) {
            LawInfoItem::firstOrCreate(
                ['name' => $item['name']],
                $item
            );
        }
    }
}