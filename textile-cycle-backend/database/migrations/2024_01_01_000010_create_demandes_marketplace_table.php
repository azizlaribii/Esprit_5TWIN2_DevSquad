<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandes_marketplace', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('articles_marketplace')->onDelete('cascade');
            $table->foreignId('acheteur_id')->constrained('users')->onDelete('cascade');
            $table->enum('statut', ['en_attente', 'acceptee', 'refusee', 'finalisee'])->default('en_attente');
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandes_marketplace');
    }
};
