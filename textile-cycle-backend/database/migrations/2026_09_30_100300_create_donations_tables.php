<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category')->nullable();  // rempli par l'IA ou le donateur
            $table->string('age_group')->nullable();
            $table->string('size')->nullable();
            $table->string('gender')->nullable();
            $table->string('season')->nullable();
            $table->string('condition')->nullable();
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->string('city');
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('status', 30)->default('pending_analysis');
            $table->json('ai_metadata')->nullable();
            $table->timestamp('analyzed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('status');
        });

        Schema::create('donation_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donation_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->timestamps();
        });

        Schema::create('donation_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('association_id')->constrained()->cascadeOnDelete();
            $table->foreignId('association_need_id')->nullable()->constrained('association_needs')->nullOnDelete();
            $table->decimal('score', 5, 2);
            $table->json('reasons')->nullable();
            $table->text('explanation')->nullable();
            $table->unsignedSmallInteger('quantity')->default(1); // copie de la quantité du don
            $table->string('status', 20)->default('suggested');   // suggested | requested | accepted | rejected | completed
            $table->string('meeting_type', 20)->nullable();       // depot | collecte
            $table->dateTime('meeting_at')->nullable();
            $table->text('meeting_note')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['donation_id', 'association_id']);
            $table->index(['association_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donation_matches');
        Schema::dropIfExists('donation_photos');
        Schema::dropIfExists('donations');
    }
};
