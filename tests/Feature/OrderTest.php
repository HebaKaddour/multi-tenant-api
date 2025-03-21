<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\Product;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OrderTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_create_order()
    {

        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $product = Product::create([
            'name' => 'Product 1',
            'tenant_id' => $tenant->id,
            'price' => 100,
            'stock_quantity' => 50,
            'description' => 'Test description',
        ]);
        $this->actingAs($user, 'api');

        $data = [
            'product_id' => $product->id,
            'quantity' => 2,
        ];

        $response = $this->postJson('/api/orders', $data);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'product_id' => $product->id,
                'quantity' => 2,
                'total_price' => 200,
                'status' => 'pending',
            ]);

        $this->assertDatabaseHas('orders', [
            'product_id' => $product->id,
            'quantity' => 2,
            'total_price' => 200,
        ]);
    }


    public function test_can_get_orders()
    {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $product = Product::create([
            'name' => 'Product 1',
            'tenant_id' => $tenant->id,
            'price' => 100,
            'stock_quantity' => 50,
            'description' => 'Test description',
        ]);
        $this->actingAs($user, 'api');


        Order::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 2,
            'total_price' => 200,
            'status' => 'pending',
            'tenant_id' => $tenant->id,
        ]);
        Order::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 1,
            'total_price' => 100,
            'status' => 'completed',
            'tenant_id' => $tenant->id,
        ]);


        $response = $this->getJson('/api/orders');


        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['status' => 'pending'])
            ->assertJsonFragment(['status' => 'completed']);
    }
}
