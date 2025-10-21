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
        Schema::create('api_repositories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->json('features');
            $table->string('pricing');
            $table->decimal('documentation_quality', 3, 1)->default(0);
            $table->decimal('community_rating', 3, 1)->default(0);
            $table->text('description')->nullable();
            $table->string('website_url')->nullable();
            $table->string('documentation_url')->nullable();
            $table->json('tags')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['category', 'is_active']);
            $table->index(['pricing', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_repositories');
    }
};