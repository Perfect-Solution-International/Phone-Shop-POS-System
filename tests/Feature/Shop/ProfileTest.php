<?php

namespace Tests\Feature\Shop;

use App\Models\Platform\Shop;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Shop $shop;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);

        $this->shop = Shop::factory()->create([
            'name' => 'Apex Cellular',
        ]);

        $this->user = User::factory()->create([
            'shop_id' => $this->shop->id,
            'name' => 'Kasun Chamara',
            'email' => 'kasun@apexcellular.lk',
            'phone' => '0771234567',
            'password' => Hash::make('Secret123!'),
        ]);

        $role = Role::firstOrCreate(['name' => 'Administrator', 'guard_name' => 'web']);
        $this->user->assignRole($role);
    }

    public function test_authenticated_user_can_view_profile_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('shop.profile'));

        $response->assertOk();
        $response->assertSee('Kasun Chamara');
        $response->assertSee('kasun@apexcellular.lk');
        $response->assertSee('Apex Cellular');
        $response->assertSee('Administrator');
    }

    public function test_user_can_update_profile_details(): void
    {
        $response = $this->actingAs($this->user)->put(route('shop.profile.update'), [
            'name' => 'Kasun Seneviratne',
            'email' => 'kasun.s@apexcellular.lk',
            'phone' => '0719876543',
        ]);

        $response->assertRedirect(route('shop.profile'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Kasun Seneviratne',
            'email' => 'kasun.s@apexcellular.lk',
            'phone' => '0719876543',
        ]);
    }

    public function test_user_cannot_update_email_to_an_existing_email(): void
    {
        User::factory()->create([
            'email' => 'other@shop.lk',
        ]);

        $response = $this->actingAs($this->user)->put(route('shop.profile.update'), [
            'name' => 'Kasun Chamara',
            'email' => 'other@shop.lk',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_user_can_update_security_password_with_valid_current_password(): void
    {
        $response = $this->actingAs($this->user)->put(route('shop.profile.password'), [
            'current_password' => 'Secret123!',
            'password' => 'NewPassword123#',
            'password_confirmation' => 'NewPassword123#',
        ]);

        $response->assertRedirect(route('shop.profile'));
        $response->assertSessionHas('success');

        $this->user->refresh();
        $this->assertTrue(Hash::check('NewPassword123#', $this->user->password));
    }

    public function test_user_cannot_update_password_with_invalid_current_password(): void
    {
        $response = $this->actingAs($this->user)->put(route('shop.profile.password'), [
            'current_password' => 'WrongPassword',
            'password' => 'NewPassword123#',
            'password_confirmation' => 'NewPassword123#',
        ]);

        $response->assertSessionHasErrors('current_password');
    }

    public function test_unauthenticated_user_cannot_access_profile(): void
    {
        $response = $this->get(route('shop.profile'));
        $response->assertRedirect(route('login'));
    }
}

