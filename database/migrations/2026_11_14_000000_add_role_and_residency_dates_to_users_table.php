<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const MOVE_DATES_CHECK = 'users_move_dates_check';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Keep historic migration values independent from future enum changes.
            $table->enum('role', ['admin', 'resident'])
                ->default('resident')
                ->after('password');
            // Add this as nullable first so an existing development user can be backfilled.
            $table->date('move_in_date')->nullable()->after('role');
            $table->date('move_out_date')->nullable()->after('move_in_date');
        });

        DB::statement(<<<'SQL'
            UPDATE users
            SET move_in_date = COALESCE(DATE(created_at), '2026-11-14')
            WHERE move_in_date IS NULL
        SQL);

        DB::statement('ALTER TABLE users MODIFY move_in_date DATE NOT NULL');

        DB::statement(sprintf(
            'ALTER TABLE users ADD CONSTRAINT %s CHECK (move_out_date IS NULL OR move_out_date >= move_in_date)',
            self::MOVE_DATES_CHECK,
        ));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement(sprintf(
            'ALTER TABLE users DROP CHECK %s',
            self::MOVE_DATES_CHECK,
        ));

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'move_in_date', 'move_out_date']);
        });
    }
};
