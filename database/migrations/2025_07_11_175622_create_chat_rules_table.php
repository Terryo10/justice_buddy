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
        Schema::create('chat_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('rule_text');
            $table->enum('type', ['instruction', 'constraint', 'context', 'guideline'])->default('instruction');
            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('model_name')->nullable();
            $table->timestamps();
            
            // Add indexes for better performance
            $table->index(['is_active', 'priority']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_rules');
    }
};
