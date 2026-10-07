<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('depots', function (Blueprint $table) {
            $table->string('ai_type')->nullable()->after('photo');
            $table->string('ai_couleur')->nullable()->after('ai_type');
            $table->string('ai_etat')->nullable()->after('ai_couleur');
            $table->string('ai_matiere')->nullable()->after('ai_etat');
            $table->decimal('ai_confiance', 5, 2)->nullable()->after('ai_matiere');
        });
    }

    public function down(): void
    {
        Schema::table('depots', function (Blueprint $table) {
            $table->dropColumn(['ai_type', 'ai_couleur', 'ai_etat', 'ai_matiere', 'ai_confiance']);
        });
    }
};