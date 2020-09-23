<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Product;
use App\Order;
use App\User;

class OrderTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function testOrderSuccess()
    {
        $email = $this->faker->safeEmail;
        $pass = Hash::make('123123');

        $user = User::create([
            'email' => $email,
            'password' => $pass
        ]);

        $this->actingAs($user, 'api');

        $product = Product::create([
            'name' => 'Porkchop',
            'available_stock' => 1000
        ]);

        $orderDetails = [
            'product_id' => $product->id,
            'quantity' => 25
        ];

        $response = $this->post('/api/order', $orderDetails, ['Accept' => 'application/json']);

        $response->dump();

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'You have successfully ordered this product.'
        ]);
    }
    public function testOrderFail()
    {
        $email = $this->faker->safeEmail;
        $pass = Hash::make('123123');

        $user = User::create([
            'email' => $email,
            'password' => $pass
        ]);

        $this->actingAs($user, 'api');

        $product = Product::create([
            'name' => 'Porkchop',
            'available_stock' => 1000
        ]);

        $orderDetails = [
            'product_id' => $product->id,
            'quantity' => 2000
        ];

        $response = $this->post('/api/order', $orderDetails, ['Accept' => 'application/json']);

        $response->dump();

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Failed to order this product due to unavailability of the stock.'
        ]);
    }
}
