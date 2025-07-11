<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('letter_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('letter_template_id')->constrained()->onDelete('cascade');
            $table->string('client_name');
            $table->string('client_email')->nullable();
            $table->string('client_phone')->nullable();
            $table->json('client_matters'); // The data provided by client
            $table->longText('generated_letter')->nullable(); // AI generated content
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->text('error_message')->nullable();
            $table->string('document_path')->nullable(); // Path to generated PDF
            $table->string('request_id')->unique(); // Unique identifier for tracking
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('request_id');
            $table->index(['client_email', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letter_requests');
    }
};