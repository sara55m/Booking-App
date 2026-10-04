<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_email_unique');
        });

        DB::statement(<<<'SQL'
            ALTER TABLE users
            ADD active_email VARCHAR(255)
            GENERATED ALWAYS AS (
                CASE WHEN deleted_at IS NULL THEN email ELSE NULL END
            ) STORED
        SQL);

        Schema::table('users', function (Blueprint $table) {
            $table->unique('active_email', 'users_active_email_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_active_email_unique');
        });

        DB::statement('ALTER TABLE users DROP COLUMN active_email');

        Schema::table('users', function (Blueprint $table) {
            $table->unique('email', 'users_email_unique');
        });
    }
};
