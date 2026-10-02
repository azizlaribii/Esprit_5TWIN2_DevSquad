<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workshops', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address');
            $table->string('city')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('phone')->nullable();
            $table->float('rating')->default(0);
            $table->json('specialties')->nullable(); // ["Couture", "Fermeture", "Cuir"]
            $table->timestamps();
        });

        if (Schema::hasTable('repair_requests')) {
            Schema::table('repair_requests', function (Blueprint $table) {
                $table->foreign('workshop_id')->references('id')->on('workshops')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('repair_requests')) {
            Schema::table('repair_requests', function (Blueprint $table) {
                $table->dropForeign(['workshop_id']);
            });
        }
        Schema::dropIfExists('workshops');
    }
};
