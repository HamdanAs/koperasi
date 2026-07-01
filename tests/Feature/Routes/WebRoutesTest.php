<?php

namespace Tests\Feature\Routes;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * WB-ROUTE-01 s/d WB-ROUTE-05, WB-ROUTE-09
 */
class WebRoutesTest extends TestCase
{
    public function test_home_route_is_registered(): void
    {
        $this->assertTrue(Route::has('home'));

        $route = Route::getRoutes()->getByName('home');
        $this->assertSame('/dashboard', $route->uri());
    }

    public function test_transaction_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('transaction.loan.index'));
        $this->assertTrue(Route::has('transaction.installment.index'));
        $this->assertTrue(Route::has('transaction.deposit.index'));
        $this->assertTrue(Route::has('transaction.withdrawal.index'));
    }

    public function test_collection_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('collection.visit.index'));
        $this->assertTrue(Route::has('collection.foreclosure.index'));
    }

    public function test_resource_verbs_use_indonesian_create_path(): void
    {
        $route = Route::getRoutes()->getByName('user.create');

        $this->assertStringContainsString('baru', $route->uri());
    }

    public function test_login_route_is_accessible(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_print_routes_exist_for_modules(): void
    {
        $this->assertTrue(Route::has('user.print'));
        $this->assertTrue(Route::has('customer.print'));
        $this->assertTrue(Route::has('transaction.loan.print'));
        $this->assertTrue(Route::has('transaction.deposit.print'));
    }
}
