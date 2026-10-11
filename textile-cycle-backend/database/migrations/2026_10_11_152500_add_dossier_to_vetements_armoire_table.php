<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vetements_armoire')) {
            Schema::table('vetements_armoire', function (Blueprint $table) {
                if (!Schema::hasColumn('vetements_armoire', 'dossier')) {
                    $table->string('dossier')->nullable()->default('T-shirt')->after('categorie');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('vetements_armoire') && Schema::hasColumn('vetements_armoire', 'dossier')) {
            Schema::table('vetements_armoire', function (Blueprint $table) {
                $table->dropColumn('dossier');
            });
        }
    }
};
