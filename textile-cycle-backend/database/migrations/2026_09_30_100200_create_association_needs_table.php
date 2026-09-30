<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('association_needs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('association_id')->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->string('age_group')->nullable(); // null = tous
            $table->string('size')->nullable();      // null = toutes
            $table->string('gender')->nullable();    // null ou mixte = tous
            $table->string('season')->nullable();    // null ou toutes = toutes
            $table->unsignedInteger('quantity_needed');
            $table->unsignedTinyInteger('urgency')->default(3); // 1 (faible) à 5 (critique)
            $table->date('expires_at')->nullable();
            $table->timestamps();

            $table->index(['association_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('association_needs');
    }
};
