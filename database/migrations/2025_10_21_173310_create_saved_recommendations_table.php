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
        Schema::create('saved_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('project_description', 2000)->nullable();
            $table->text('requirements_summary')->nullable();
            $table->json('requirements_data')->nullable();
            $table->json('recommendations_data');
            $table->string('source_type')->default('text'); // 'text' or 'file'
            $table->string('original_filename')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_recommendations');
    }
};
