<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->string('slug')->unique()->after('name');
            $table->text('description')->nullable()->after('slug');
            $table->string('file_path')->after('description');
            $table->string('file_name')->after('file_path');
            $table->string('file_type')->after('file_name');
            $table->string('file_extension')->after('file_type');
            $table->unsignedBigInteger('file_size')->after('file_extension');
            $table->string('category')->after('file_size');
            $table->json('tags')->nullable()->after('category');
            $table->unsignedInteger('download_count')->default(0)->after('tags');
            $table->boolean('is_active')->default(true)->after('download_count');
            $table->boolean('is_featured')->default(false)->after('is_active');
            $table->unsignedInteger('sort_order')->default(0)->after('is_featured');
            $table->softDeletes()->after('updated_at');
            
            // Indexes
            $table->index(['is_active', 'category']);
            $table->index(['is_active', 'is_featured']);
            $table->index(['is_active', 'download_count']);
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex(['sort_order']);
            $table->dropIndex(['is_active', 'download_count']);
            $table->dropIndex(['is_active', 'is_featured']);
            $table->dropIndex(['is_active', 'category']);
            
            $table->dropSoftDeletes();
            $table->dropColumn([
                'name', 'slug', 'description', 'file_path', 'file_name', 
                'file_type', 'file_extension', 'file_size', 'category', 
                'tags', 'download_count', 'is_active', 'is_featured', 'sort_order'
            ]);
        });
    }
};
