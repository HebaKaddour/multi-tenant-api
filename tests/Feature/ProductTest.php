<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Product;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProductTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use DatabaseTransactions;

    public function test_can_get_products()
    {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->actingAs($user, 'api');

        $product1 = Product::create([
            'name' => 'Product 1',
            'tenant_id' => $tenant->id,
            'price' => 100,
            'stock_quantity' => 10,
            'description' => 'This is Product 1'
        ]);
        $product2 = Product::create([
            'name' => 'Product 2',
            'tenant_id' => $tenant->id,
            'price' => 150,
            'stock_quantity' => 5,
            'description' => 'This is Product 2'
        ]);


        $response = $this->getJson('/api/products');


        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['name' => 'Product 1'])
            ->assertJsonFragment(['name' => 'Product 2']);
    }

    public function test_can_create_product()
{
    $tenant = Tenant::create(['name' => 'Test Tenant']);
    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $this->actingAs($user, 'api');

    $data = [
        'name' => 'New Product',
        'description' => 'Description for new product',
        'price' => 200,
        'stock_quantity' => 20
    ];

    $response = $this->postJson('/api/products', $data);  // تأكد من أن هذا هو المسار الصحيح

    $response->assertStatus(201)
        ->assertJsonFragment(['name' => 'New Product'])
        ->assertJsonFragment(['price' => 200])
        ->assertJsonFragment(['stock_quantity' => 20]);
}

    public function test_can_update_product()
    {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->actingAs($user, 'api');

        $product = Product::create([
            'name' => 'Old Product',
            'tenant_id' => $tenant->id,
            'price' => 100,
            'stock_quantity' => 10,
            'description' => 'Old description'
        ]);

        $data = [
            'name' => 'Updated Product',
            'description' => 'Updated description',
            'price' => 250,
            'stock_quantity' => 30
        ];

        $response = $this->putJson("/api/products/{$product->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Product'])
            ->assertJsonFragment(['price' => 250])
            ->assertJsonFragment(['stock_quantity' => 30])
            ->assertJsonFragment(['description' => 'Updated description']);
    }

    public function test_can_delete_product()
    {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->actingAs($user, 'api');

        $product = Product::create([
            'name' => 'Product to Delete',
            'tenant_id' => $tenant->id,
            'price' => 100,
            'stock_quantity' => 10,
            'description' => 'Product description'
        ]);

        $response = $this->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'product delete successfuly']);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }



}
