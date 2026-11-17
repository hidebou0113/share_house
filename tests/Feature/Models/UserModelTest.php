<?php

namespace Tests\Feature\Models;

use App\Enums\UserRole;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_stores_role_and_residency_dates_with_expected_casts(): void
    {
        $user = new User([
            'name' => 'Admin Resident',
            'email' => 'admin@example.com',
            'password' => 'a-secure-password',
        ]);
        $user->role = UserRole::Admin;
        $user->move_in_date = '2026-01-05';
        $user->move_out_date = null;
        $user->save();

        $this->assertSame(UserRole::Admin, $user->role);
        $this->assertSame('2026-01-05', $user->move_in_date->toDateString());
        $this->assertNull($user->move_out_date);
        $this->assertTrue(Hash::check('a-secure-password', $user->password));
    }

    public function test_role_defaults_to_resident_when_it_is_not_specified(): void
    {
        $attributes = $this->userAttributes([
            'email' => 'existing@example.com',
        ]);
        unset($attributes['role']);

        DB::table('users')->insert($attributes);

        $user = User::query()->where('email', 'existing@example.com')->firstOrFail();

        $this->assertSame(UserRole::Resident, $user->role);
        $this->assertSame('2026-01-05', $user->move_in_date->toDateString());
        $this->assertNull($user->move_out_date);
    }

    public function test_privileged_attributes_cannot_be_mass_assigned(): void
    {
        $this->expectException(MassAssignmentException::class);

        User::create([
            'name' => 'Resident',
            'email' => 'resident@example.com',
            'password' => 'a-secure-password',
            'role' => UserRole::Admin,
            'move_in_date' => '2026-01-05',
        ]);
    }

    public function test_factory_can_create_an_admin_who_is_also_a_resident_account(): void
    {
        $user = User::factory()->admin()->create();

        $this->assertSame(UserRole::Admin, $user->role);
        $this->assertNotNull($user->move_in_date);
    }

    public function test_database_rejects_an_unknown_role(): void
    {
        $this->expectException(QueryException::class);

        DB::table('users')->insert($this->userAttributes([
            'email' => 'unknown-role@example.com',
            'role' => 'owner',
        ]));
    }

    public function test_database_rejects_a_move_out_date_before_the_move_in_date(): void
    {
        $this->expectException(QueryException::class);

        DB::table('users')->insert($this->userAttributes([
            'email' => 'invalid-dates@example.com',
            'move_in_date' => '2026-04-10',
            'move_out_date' => '2026-04-09',
        ]));
    }

    public function test_database_requires_a_move_in_date(): void
    {
        $this->expectException(QueryException::class);

        DB::table('users')->insert($this->userAttributes([
            'email' => 'missing-move-in@example.com',
            'move_in_date' => null,
        ]));
    }

    public function test_php_and_mysql_role_definitions_are_consistent(): void
    {
        $column = DB::selectOne(
            <<<'SQL'
                SELECT COLUMN_TYPE AS column_type
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'users'
                  AND COLUMN_NAME = 'role'
            SQL,
        );
        $expectedValues = implode("','", array_column(UserRole::cases(), 'value'));

        $this->assertNotNull($column);
        $this->assertSame("enum('{$expectedValues}')", $column->column_type);
    }

    public function test_residency_period_includes_both_move_in_and_move_out_dates(): void
    {
        $user = User::factory()->make([
            'move_in_date' => '2026-01-05',
            'move_out_date' => '2026-01-31',
        ]);

        $this->assertFalse($user->isResidentOn(CarbonImmutable::parse('2026-01-04', 'Asia/Tokyo')));
        $this->assertTrue($user->isResidentOn(CarbonImmutable::parse('2026-01-05', 'Asia/Tokyo')));
        $this->assertTrue($user->isResidentOn(CarbonImmutable::parse('2026-01-31', 'Asia/Tokyo')));
        $this->assertFalse($user->isResidentOn(CarbonImmutable::parse('2026-02-01', 'Asia/Tokyo')));
    }

    public function test_resident_without_a_move_out_date_remains_active(): void
    {
        $user = User::factory()->make([
            'move_in_date' => '2026-01-05',
            'move_out_date' => null,
        ]);

        $this->assertTrue($user->isResidentOn(CarbonImmutable::parse('2030-01-01', 'Asia/Tokyo')));
    }

    public function test_database_seeder_does_not_create_a_known_credential(): void
    {
        $this->seed();

        $this->assertDatabaseCount('users', 0);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function userAttributes(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Resident',
            'email' => 'resident@example.com',
            'password' => Hash::make('a-secure-password'),
            'role' => 'resident',
            'move_in_date' => '2026-01-05',
            'move_out_date' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides);
    }
}
