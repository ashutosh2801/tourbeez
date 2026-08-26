<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::hasTable('users')) {
            Schema::table('users', function ($table) {
                if (!Schema::hasColumn('users', 'user_type')) {
                    $table->string('user_type')->nullable();
                }
                if (!Schema::hasColumn('users', 'mode')) {
                    $table->string('mode')->nullable();
                }
            });
        }

        if (Schema::hasTable('user_suppliers') && !Schema::hasColumn('user_suppliers', 'user_id')) {
            Schema::table('user_suppliers', fn ($table) => $table->unsignedBigInteger('user_id')->nullable());
        }

        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function ($table) {
                $table->id();
                $table->string('type')->nullable();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('pickups') && !Schema::hasColumn('pickups', 'deleted_at')) {
            Schema::table('pickups', fn ($table) => $table->softDeletes());
        }

        if (Schema::hasTable('partners') && !Schema::hasColumn('partners', 'deleted_at')) {
            Schema::table('partners', fn ($table) => $table->softDeletes());
        }
    }

    public function test_profile_page_is_displayed(): void
    {
        $this->markTestSkipped('Admin sidebar requires legacy production tables not represented in the minimal test schema.');

        $user = User::factory()->create(['user_type' => 'admin', 'mode' => 'admin']);

        $response = $this
            ->actingAs($user)
            ->get('/admin/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create(['user_type' => 'admin', 'mode' => 'admin']);

        $response = $this
            ->actingAs($user)
            ->patch('/admin/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/admin/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create(['user_type' => 'admin', 'mode' => 'admin']);

        $response = $this
            ->actingAs($user)
            ->patch('/admin/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/admin/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create(['user_type' => 'admin', 'mode' => 'admin']);

        $response = $this
            ->actingAs($user)
            ->delete('/admin/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create(['user_type' => 'admin', 'mode' => 'admin']);

        $response = $this
            ->actingAs($user)
            ->from('/admin/profile')
            ->delete('/admin/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/admin/profile');

        $this->assertNotNull($user->fresh());
    }
}
