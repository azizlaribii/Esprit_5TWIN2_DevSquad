<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 2e table du module Upcycling : les catégories de projet (gérées par l'admin).
 * Relation 1-N : une catégorie contient plusieurs transformations.
 * Les anciens projets sont rattachés à la catégorie du même nom que leur ancien « type_projet ».
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories_upcycling', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();
            $table->string('description')->nullable();
            $table->string('icone')->default('category');
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        $now = now();
        $defaults = [
            ['Sac', 'Sacs, tote bags et pochettes', 'shopping_bag'],
            ['Coussin', 'Housses de coussin et poufs', 'weekend'],
            ['Accessoire', 'Bandeaux, porte-clés, trousses', 'watch'],
            ['Décoration', 'Tapis, rideaux, objets déco', 'palette'],
            ['Vêtement', 'Nouveau vêtement à partir d\'un ancien', 'checkroom'],
            ['Autre', 'Autres idées de transformation', 'category'],
        ];

        foreach ($defaults as [$nom, $description, $icone]) {
            DB::table('categories_upcycling')->insert([
                'nom' => $nom, 'description' => $description, 'icone' => $icone,
                'actif' => true, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        Schema::table('transformations', function (Blueprint $table) {
            $table->foreignId('categorie_id')->nullable()->after('depot_id')
                ->constrained('categories_upcycling')->restrictOnDelete();
        });

        // Rattache les projets existants à la catégorie du même nom
        foreach (DB::table('categories_upcycling')->get() as $categorie) {
            DB::table('transformations')
                ->where('type_projet', $categorie->nom)
                ->update(['categorie_id' => $categorie->id]);
        }
    }

    public function down(): void
    {
        Schema::table('transformations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('categorie_id');
        });
        Schema::dropIfExists('categories_upcycling');
    }
};
