<?php

namespace Tests\Unit\Authentication;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create([
            'name' => 'Programs Manager',
            'slug' => 'programs-manager',
            'description' => 'Full system access',
        ]);
    }

    public function test_user_is_locked_when_locked_until_is_in_future(): void
    {
        $role = Role::where('slug', 'programs-manager')->first();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@canzim.org',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
        ]);

        $user->locked_until = now()->addMinutes(15);
        $user->save();
        $user->refresh();

        $this->assertTrue($user->isLocked());
    }

    public function test_user_is_not_locked_when_locked_until_is_in_past(): void
    {
        $role = Role::where('slug', 'programs-manager')->first();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@canzim.org',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
            'locked_until' => now()->subMinutes(15),
        ]);

        $this->assertFalse($user->isLocked());
    }

    public function test_user_is_not_locked_when_locked_until_is_null(): void
    {
        $role = Role::where('slug', 'programs-manager')->first();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@canzim.org',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
            'locked_until' => null,
        ]);

        $this->assertFalse($user->isLocked());
    }

    public function test_user_is_active_when_status_is_active(): void
    {
        $role = Role::where('slug', 'programs-manager')->first();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@canzim.org',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        $this->assertTrue($user->isActive());
    }

    public function test_user_is_not_active_when_status_is_inactive(): void
    {
        $role = Role::where('slug', 'programs-manager')->first();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@canzim.org',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
            'status' => 'inactive',
        ]);

        $this->assertFalse($user->isActive());
    }

    public function test_increment_failed_login_attempts_increases_counter(): void
    {
        $role = Role::where('slug', 'programs-manager')->first();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@canzim.org',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
            'failed_login_attempts' => 0,
        ]);

        $user->incrementFailedLoginAttempts();

        $this->assertEquals(1, $user->failed_login_attempts);
        $this->assertNotNull($user->last_failed_login_at);
    }

    public function test_reset_failed_login_attempts_clears_counter(): void
    {
        $role = Role::where('slug', 'programs-manager')->first();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@canzim.org',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
            'failed_login_attempts' => 3,
            'last_failed_login_at' => now(),
        ]);

        $user->resetFailedLoginAttempts();

        $this->assertEquals(0, $user->failed_login_attempts);
        $this->assertNull($user->last_failed_login_at);
    }

    public function test_lock_account_sets_locked_until(): void
    {
        $role = Role::where('slug', 'programs-manager')->first();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@canzim.org',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
        ]);

        $user->lockAccount(15);

        $this->assertNotNull($user->locked_until);
        $this->assertTrue($user->isLocked());
    }

    public function test_update_last_login_sets_timestamp_and_ip(): void
    {
        $role = Role::where('slug', 'programs-manager')->first();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@canzim.org',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
        ]);

        $user->updateLastLogin('192.168.1.1');

        $this->assertNotNull($user->last_login_at);
        $this->assertEquals('192.168.1.1', $user->last_login_ip);
    }

    public function test_has_role_returns_true_for_matching_role(): void
    {
        $role = Role::where('slug', 'programs-manager')->first();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@canzim.org',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
        ]);

        $this->assertTrue($user->hasRole('programs-manager'));
    }

    public function test_has_role_returns_false_for_non_matching_role(): void
    {
        $role = Role::where('slug', 'programs-manager')->first();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@canzim.org',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
        ]);

        $this->assertFalse($user->hasRole('finance-officer'));
    }

    public function test_verify_password_returns_true_for_correct_password(): void
    {
        $role = Role::where('slug', 'programs-manager')->first();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@canzim.org',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
        ]);

        $this->assertTrue($user->verifyPassword('password123'));
    }

    public function test_verify_password_returns_false_for_incorrect_password(): void
    {
        $role = Role::where('slug', 'programs-manager')->first();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@canzim.org',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
        ]);

        $this->assertFalse($user->verifyPassword('wrongpassword'));
    }
}
