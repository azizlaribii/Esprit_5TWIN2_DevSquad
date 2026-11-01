<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['depots', 'valorisations'] as $nomTable) {
            Schema::table($nomTable, function (Blueprint $table) {
                $table->text('motif_refus')->nullable()->after('statut');
                $table->foreignId('traite_par')->nullable()->after('motif_refus')
                      ->constrained('users')->nullOnDelete();
                $table->timestamp('traite_le')->nullable()->after('traite_par');
            });
        }
    }

    public function down(): void
    {
        foreach (['depots', 'valorisations'] as $nomTable) {
            Schema::table($nomTable, function (Blueprint $table) {
                $table->dropConstrainedForeignId('traite_par');
                $table->dropColumn(['motif_refus', 'traite_le']);
            });
        }
    }
};
