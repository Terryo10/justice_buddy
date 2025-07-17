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
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->string('type')->default('string'); // string, integer, float, boolean, array, json
            $table->string('group')->default('general'); // general, ai, api, mail, storage
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false); // If setting should be available via API
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['group', 'key']);
            $table->index('is_public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};