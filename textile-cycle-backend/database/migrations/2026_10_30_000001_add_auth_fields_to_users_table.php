<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            }

            if (!Schema::hasColumn('users', 'two_factor_secret')) {
                $table->text('two_factor_secret')->nullable()->after('password');
            }

            if (!Schema::hasColumn('users', 'two_factor_recovery_codes')) {
                $table->text('two_factor_recovery_codes')->nullable();
            }

            if (!Schema::hasColumn('users', 'two_factor_enabled')) {
                $table->boolean('two_factor_enabled')->default(false);
            }

            if (!Schema::hasColumn('users', 'avatar')) {
                $avatar = $table->string('avatar')->nullable();
                if (Schema::hasColumn('users', 'role')) {
                    $avatar->after('role');
                }
            }
        });
    }

    public function down(): void
    {
        $columns = array_filter(
            ['email_verified_at', 'two_factor_enabled', 'avatar'],
            fn ($column) => Schema::hasColumn('users', $column)
        );

        if (!empty($columns)) {
            Schema::table('users', function (Blueprint $table) use ($columns) {
                $table->dropColumn(array_values($columns));
            });
        }
    }
};