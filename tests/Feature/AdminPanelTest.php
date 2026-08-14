<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\FeatureFlag;
use App\Models\UserFeatureOverride;
use App\Models\Subscription;
use App\Models\Plan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_admin_dashboard()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_access_admin_dashboard()
    {
        $admin = User::factory()->create();
        $role = Role::create(['name' => 'super_admin']);
        $admin->roles()->attach($role->id);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
    }

    public function test_artisan_admin_create_command()
    {
        Artisan::call('admin:create', [
            '--no-interaction' => true,
        ]);

        $this->assertDatabaseHas('roles', ['name' => 'super_admin']);
    }

    public function test_feature_override_priority()
    {
        $user = User::factory()->create([
            'is_skills_paid' => false,
        ]);

        // Assert no skills access initially
        $this->assertFalse($user->hasSkillsAccess());

        // Create override
        UserFeatureOverride::create([
            'user_id' => $user->id,
            'feature_key' => 'skills',
            'enabled' => true,
            'reason' => 'Granted S-rank override',
        ]);

        // Reload user and assert skills access is now true due to override
        $user->load('featureOverrides');
        $this->assertTrue($user->hasSkillsAccess());
    }

    public function test_global_registration_toggle()
    {
        // Globally disable registration
        $flag = FeatureFlag::firstOrCreate(['feature_key' => 'registration']);
        $flag->update(['enabled' => false]);

        $response = $this->get(route('register'));

        // Register view should assert visual lock or return confirmation block
        $response->assertSee('REGISTRATION GATEWAY: LOCKED');
    }
}
