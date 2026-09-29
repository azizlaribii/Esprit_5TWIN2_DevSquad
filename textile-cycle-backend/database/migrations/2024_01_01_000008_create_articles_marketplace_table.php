<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles_marketplace', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('titre');
            $table->text('description');
            $table->string('categorie');
            $table->string('marque')->nullable();
            $table->string('taille');
            $table->string('genre'); // Homme, Femme, Enfant, Unisexe
            $table->string('etat');  // Neuf avec étiquette, Très bon état, Bon état, État correct
            $table->enum('type', ['vente', 'echange', 'don'])->default('vente');
            $table->decimal('prix', 8, 2)->nullable();
            $table->string('article_echange')->nullable(); // Pour les échanges
            $table->string('image_url')->nullable();
            $table->enum('statut', ['disponible', 'en_cours', 'vendu', 'retire'])->default('disponible');
            $table->integer('ai_score')->default(0);        // Score IA de compatibilité
            $table->decimal('ai_prix_min', 8, 2)->nullable(); // Estimation IA prix min
            $table->decimal('ai_prix_max', 8, 2)->nullable(); // Estimation IA prix max
            $table->string('ai_classification')->nullable();   // Classification automatique IA
            $table->decimal('note_vendeur', 3, 2)->nullable();
            $table->integer('vues')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles_marketplace');
    }
};
