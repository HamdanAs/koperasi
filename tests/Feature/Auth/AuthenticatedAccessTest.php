<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * WB-ROUTE-06 s/d WB-ROUTE-08
 */
class AuthenticatedAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_from_protected_routes(): void
    {
        $response = $this->get('/transaksi/pinjaman');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::create([
            'name' => 'Tester',
            'username' => 'tester',
            'password' => Hash::make('secret'),
            'role' => 'teller',
            'phone' => '081234567890',
            'gender' => 'L',
            'birth' => '1990-01-01',
            'last_education' => 'S1',
            'joined_at' => now(),
            'address' => 'Alamat uji',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }
}
