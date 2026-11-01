<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valorisations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('depot_id')->constrained('depots')->cascadeOnDelete();
            $table->string('filiere');                       // revente, reparation, don, recyclage
            $table->string('statut')->default('propose');    // propose, validee, realisee
            $table->decimal('estimation_prix', 8, 2)->nullable();
            $table->text('commentaire')->nullable();
            $table->string('source')->default('manuelle');   // ia, manuelle
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valorisations');
    }
};