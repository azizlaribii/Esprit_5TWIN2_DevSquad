<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repair_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('workshop_id')->nullable();

            $table->string('photo_path');

            // Résultat de l'analyse IA
            $table->string('defect_type')->nullable();     // Trou, Déchirure, Tache, Fermeture cassée, Bouton manquant, Usure
            $table->string('location')->nullable();        // Genou, Manche, Col, ...
            $table->enum('severity', ['faible', 'moyenne', 'elevee'])->nullable();
            $table->json('suggested_repairs')->nullable();  // ["Couture", "Renforcement du tissu"]
            $table->decimal('estimated_cost_min', 8, 2)->nullable();
            $table->decimal('estimated_cost_max', 8, 2)->nullable();
            $table->float('confidence')->nullable();        // score de confiance IA (0-1)

            $table->enum('status', [
                'en_attente',       // photo envoyée, analyse pas encore faite
                'analysee',         // IA a rendu un résultat
                'atelier_choisi',   // utilisateur a choisi un atelier
                'en_cours',
                'terminee',
                'annulee',
            ])->default('en_attente');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_requests');
    }
};
