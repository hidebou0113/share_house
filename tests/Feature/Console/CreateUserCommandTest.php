<?php

namespace Tests\Feature\Console;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateUserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_admin_resident_with_a_hidden_password_prompt(): void
    {
        $this->artisan('app:create-user')
            ->expectsQuestion('Name', 'Admin Resident')
            ->expectsQuestion('Email', 'ADMIN@EXAMPLE.COM')
            ->expectsChoice('Role', 'admin', ['admin', 'resident'])
            ->expectsQuestion('Move-in date (YYYY-MM-DD)', '2026-01-05')
            ->expectsQuestion('Password', 'a-secure-password')
            ->expectsQuestion('Confirm password', 'a-secure-password')
            ->expectsOutputToContain('User created successfully.')
            ->assertSuccessful();

        $user = User::query()->sole();

        $this->assertSame('Admin Resident', $user->name);
        $this->assertSame('admin@example.com', $user->email);
        $this->assertSame(UserRole::Admin, $user->role);
        $this->assertSame('2026-01-05', $user->move_in_date->toDateString());
        $this->assertNull($user->move_out_date);
        $this->assertTrue(Hash::check('a-secure-password', $user->password));
        $this->assertNotSame('a-secure-password', $user->password);
    }

    public function test_it_rejects_a_duplicate_email_without_creating_another_user(): void
    {
        User::factory()->create([
            'email' => 'resident@example.com',
        ]);

        $this->artisan('app:create-user')
            ->expectsQuestion('Name', 'Another Resident')
            ->expectsQuestion('Email', 'RESIDENT@EXAMPLE.COM')
            ->expectsChoice('Role', 'resident', ['admin', 'resident'])
            ->expectsQuestion('Move-in date (YYYY-MM-DD)', '2026-02-02')
            ->expectsQuestion('Password', 'another-password')
            ->expectsQuestion('Confirm password', 'another-password')
            ->assertFailed();

        $this->assertDatabaseCount('users', 1);
    }

    public function test_it_rejects_an_invalid_move_in_date(): void
    {
        $this->artisan('app:create-user')
            ->expectsQuestion('Name', 'Resident')
            ->expectsQuestion('Email', 'resident@example.com')
            ->expectsChoice('Role', 'resident', ['admin', 'resident'])
            ->expectsQuestion('Move-in date (YYYY-MM-DD)', '2026-02-30')
            ->expectsQuestion('Password', 'a-secure-password')
            ->expectsQuestion('Confirm password', 'a-secure-password')
            ->assertFailed();

        $this->assertDatabaseCount('users', 0);
    }

    public function test_it_rejects_a_short_or_unconfirmed_password(): void
    {
        $this->artisan('app:create-user')
            ->expectsQuestion('Name', 'Resident')
            ->expectsQuestion('Email', 'resident@example.com')
            ->expectsChoice('Role', 'resident', ['admin', 'resident'])
            ->expectsQuestion('Move-in date (YYYY-MM-DD)', '2026-02-02')
            ->expectsQuestion('Password', 'short')
            ->expectsQuestion('Confirm password', 'different')
            ->assertFailed();

        $this->assertDatabaseCount('users', 0);
    }

    public function test_password_is_not_available_as_a_command_argument_or_option(): void
    {
        $definition = Artisan::all()['app:create-user']->getDefinition();

        $this->assertFalse($definition->hasArgument('password'));
        $this->assertFalse($definition->hasOption('password'));
    }
}
