<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\User;

class RegisterTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function testUserRegisterSuccess()
    {
        $email = $this->faker->safeEmail;

        $details = [
            'name' => $this->faker->name,
            'email' => $email, 
            'password' => '123123', 
            'password_confirmation' => '123123'
        ];

        $response = $this->post('/api/register', $details, ['Accept' => 'application/json']);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'User successfully registered! Check your email to verify the account.'
        ]);
    }

    public function testUserRegisterFail()
    {
        $email = $this->faker->safeEmail;
        
        factory(\App\User::class)->create(['email'=>$email]);

        $details = [
            'name' => $this->faker->name,
            'email' => $email, 
            'password' => '123123', 
            'password_confirmation' => '123123'
        ];

        $response = $this->post('/api/register', $details, ['Accept' => 'application/json']);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Email already taken.'
        ]);
    }
}
