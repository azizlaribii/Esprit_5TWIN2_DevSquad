<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ajouter l'acheteur sur la table des articles marketplace
        if (Schema::hasTable('articles_marketplace')) {
            Schema::table('articles_marketplace', function (Blueprint $table) {
                if (!Schema::hasColumn('articles_marketplace', 'acheteur_id')) {
                    $table->foreignId('acheteur_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
                }
            });
        }

        // 2. Créer la table pour la section Armoire (dressing virtuel de l'utilisateur)
        if (!Schema::hasTable('vetements_armoire')) {
            Schema::create('vetements_armoire', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('titre')->nullable();
                $table->string('image_path');
                $table->string('categorie')->nullable();
                $table->string('taille')->nullable();
                $table->string('genre')->nullable();
                $table->string('etat')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('articles_marketplace') && Schema::hasColumn('articles_marketplace', 'acheteur_id')) {
            Schema::table('articles_marketplace', function (Blueprint $table) {
                $table->dropForeign(['acheteur_id']);
                $table->dropColumn('acheteur_id');
            });
        }

        Schema::dropIfExists('vetements_armoire');
    }
};
