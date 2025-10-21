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
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('preference_type', 50); // 'favorite_api', 'saved_search', 'notification_setting'
            $table->string('preference_key', 100);
            $table->json('preference_value');
            $table->timestamps();
            
            $table->unique(['user_id', 'preference_type', 'preference_key'], 'user_pref_unique');
            $table->index(['user_id', 'preference_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};