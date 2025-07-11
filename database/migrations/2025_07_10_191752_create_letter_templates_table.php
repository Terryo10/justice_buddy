<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('letter_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->longText('template_content'); // The template with placeholders
            $table->json('required_fields'); // Array of required field names
            $table->json('optional_fields')->nullable(); // Array of optional field names
            $table->string('category')->nullable(); // e.g., 'eviction', 'employment', 'family'
            $table->text('ai_instructions')->nullable(); // Specific instructions for AI
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['is_active', 'category']);
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letter_templates');
    }
};