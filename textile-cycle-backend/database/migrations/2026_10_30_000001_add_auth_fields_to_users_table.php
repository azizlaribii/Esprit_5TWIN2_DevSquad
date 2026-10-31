<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->string('two_factor_secret')->nullable()->after('password');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->boolean('two_factor_enabled')->default(false)->after('two_factor_recovery_codes');
            $table->string('avatar')->nullable()->after('role');
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['email_verified_at', 'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_enabled', 'avatar']);
        });
    }
};
