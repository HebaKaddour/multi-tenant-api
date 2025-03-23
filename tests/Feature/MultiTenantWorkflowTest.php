<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Product;
use Database\Factories\ProductFactory;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MultiTenantWorkflowTest extends TestCase
{
    //use RefreshDatabase;
     use DatabaseTransactions;
    /**
     * A basic feature test example.
     */
    public function test_user_registration_for_multiple_tenants()
    {

        $tenantA = Tenant::create(['name' => 'Tenant A']);
        $tenantB = Tenant::create(['name' => 'Tenant B']);

        $userA = User::create([
            'name' => 'User A',
            'email' => 'userA@example.com',
            'password' => bcrypt('password123'),
            'tenant_id' => $tenantA->id,
        ]);

        $userB = User::create([
            'name' => 'User B',
            'email' => 'userB@example.com',
            'password' => bcrypt('password123'),
            'tenant_id' => $tenantB->id,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'userA@example.com',
            'tenant_id' => $tenantA->id,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'userB@example.com',
            'tenant_id' => $tenantB->id,
        ]);
    }

    public function test_user_login_for_multiple_tenants()
    {

        $tenantA = Tenant::create(['name' => 'Tenant A']);
        $tenantB = Tenant::create(['name' => 'Tenant B']);

        $userA = User::factory()->create([
            'tenant_id' => $tenantA->id,
            'email' => 'userA@example.com',
            'password' => bcrypt('password123'),
        ]);

        $userB = User::factory()->create([
            'tenant_id' => $tenantB->id,
            'email' => 'userB@example.com',
            'password' => bcrypt('password123'),
        ]);

        $responseA = $this->postJson('/api/login', [
            'email' => 'userA@example.com',
            'password' => 'password123',
        ]);

        $responseA->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['token', 'user' => ['id', 'name', 'email', 'tenant_id']],
            ]);


        $responseB = $this->postJson('/api/login', [
            'email' => 'userB@example.com',
            'password' => 'password123',
        ]);

        $responseB->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['token', 'user' => ['id', 'name', 'email', 'tenant_id']],
            ]);
    }

    public function test_crud_operations_for_multiple_tenants()
    {

        $tenantA = Tenant::create(['name' => 'Tenant A']);
        $tenantB = Tenant::create(['name' => 'Tenant B']);


        $userA = User::factory()->create(['tenant_id' => $tenantA->id]);
        $userB = User::factory()->create(['tenant_id' => $tenantB->id]);


        $this->actingAs($userA, 'api');
        $productA = Product::create([
            'name' => 'Product A',
            'tenant_id' => $tenantA->id,
            'price' => 100,
            'stock_quantity' => 50,
        ]);

        $this->actingAs($userB, 'api');
        $productB = Product::create([
            'name' => 'Product B',
            'tenant_id' => $tenantB->id,
            'price' => 200,
            'stock_quantity' => 30,
        ]);


        $this->assertDatabaseHas('products', [
            'name' => 'Product A',
            'tenant_id' => $tenantA->id,
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Product B',
            'tenant_id' => $tenantB->id,
        ]);
    }


    public function test_create_order_for_multiple_tenants()
    {

        $tenantA = Tenant::create(['name' => 'Tenant A']);
        $tenantB = Tenant::create(['name' => 'Tenant B']);
        $userA = User::factory()->create(['tenant_id' => $tenantA->id]);
        $userB = User::factory()->create(['tenant_id' => $tenantB->id]);


        $productA = Product::create([
            'name' => 'Product A',
            'tenant_id' => $tenantA->id,
            'price' => 100,
            'stock_quantity' => 50,
        ]);

        $productB = Product::create([
            'name' => 'Product B',
            'tenant_id' => $tenantB->id,
            'price' => 200,
            'stock_quantity' => 30,
        ]);


        $this->actingAs($userA, 'api');
        $responseA = $this->postJson('/api/orders', [
            'product_id' => $productA->id,
            'quantity' => 2,
        ]);

        $responseA->assertStatus(201)
            ->assertJsonFragment([
                'product_id' => $productA->id,
                'quantity' => 2,
                'total_price' => 200,
                'status' => 'pending',
            ]);


        $this->actingAs($userB, 'api');
        $responseB = $this->postJson('/api/orders', [
            'product_id' => $productB->id,
            'quantity' => 1,
        ]);

        $responseB->assertStatus(201)
            ->assertJsonFragment([
                'product_id' => $productB->id,
                'quantity' => 1,
                'total_price' => 200,
                'status' => 'pending',
            ]);
    }
    public function test_tenants_data_is_isolated()
    {
        $tenantA = Tenant::create(['name' => 'Tenant A']);
        $tenantB = Tenant::create(['name' => 'Tenant B']);

        $userA = User::factory()->create(['tenant_id' => $tenantA->id]);
        $userB = User::factory()->create(['tenant_id' => $tenantB->id]);

        Product::factory()->create(['tenant_id' => $tenantA->id,'name'=>'companyA','price'=>200,'stock_quantity'=>5]);
        Product::factory()->create(['tenant_id' => $tenantB->id,'name'=>'companyB','price'=>200,'stock_quantity'=>5]);

        $this->actingAs($userA, 'api');
        $responseA = $this->getJson('/api/products');
        dump($responseA->json());
        $responseA->assertStatus(200)
                 // ->assertJsonCount(2)
                  ->assertJsonFragment(['tenant_id' => $tenantA->id]);

        $this->actingAs($userB, 'api');
        $responseB = $this->getJson('/api/products');
        dump($responseB->json());
        $responseB->assertStatus(200)
              //    ->assertJsonCount(2)
                  ->assertJsonFragment(['tenant_id' => $tenantB->id]);
    }

}
