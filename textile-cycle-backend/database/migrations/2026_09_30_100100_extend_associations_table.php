<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| La table `associations` existe déjà (nom, adresse, beneficiaires_aides) et alimente les
| statistiques de l'équipe. On l'ÉTEND au lieu de la recréer : ses colonnes et ses lignes
| restent intactes. Les associations existantes n'ont ni user_id ni verified_at : elles ne
| sont donc pas proposées aux donateurs tant qu'un admin ne les a pas rattachées et vérifiées.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('associations', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->unique()->after('id')->constrained()->nullOnDelete();
            $table->text('description')->nullable();
            $table->string('city')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->json('accepted_conditions')->nullable(); // ex. ["neuf","bon"] ; vide = tout accepter
            $table->json('accepted_categories')->nullable(); // vide = toutes
            $table->unsignedInteger('capacity')->default(100);
            $table->text('opening_hours')->nullable();
            $table->string('phone', 30)->nullable();
            $table->timestamp('verified_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('associations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn([
                'description', 'city', 'lat', 'lng', 'accepted_conditions', 'accepted_categories',
                'capacity', 'opening_hours', 'phone', 'verified_at',
            ]);
        });
    }
};
