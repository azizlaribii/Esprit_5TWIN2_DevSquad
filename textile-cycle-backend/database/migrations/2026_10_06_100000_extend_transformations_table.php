<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module Transformation / Upcycling.
 * On ÉTEND la table existante (créée par 2024_01_01_000005) sans la casser :
 * les anciennes lignes restent valides (colonnes ajoutées = nullables / avec défaut).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transformations', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->foreignId('depot_id')->nullable()->after('user_id')->constrained('depots')->nullOnDelete();
            $table->text('description')->nullable()->after('type_projet');
            $table->string('difficulte')->nullable()->after('description');       // facile, moyen, avance
            $table->string('duree_estimee')->nullable()->after('difficulte');     // ex. "2 h"
            $table->json('materiaux')->nullable()->after('duree_estimee');
            $table->boolean('genere_par_ia')->default(false)->after('materiaux');
        });
    }

    public function down(): void
    {
        Schema::table('transformations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropConstrainedForeignId('depot_id');
            $table->dropColumn(['description', 'difficulte', 'duree_estimee', 'materiaux', 'genere_par_ia']);
        });
    }
};
