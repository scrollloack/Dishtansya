<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\User;

class LoginTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function testUserLoginSuccess()
    {
        $email = $this->faker->safeEmail;
        $pass = Hash::make('123123');

        factory(\App\User::class)->create([
            'email' => $email,
            'password' => $pass
        ]);

        $details = [
            'email' => $email,
            'password' => '123123'
        ];

        $response = $this->post('/api/login', $details, ['Accept' => 'application/json']);

        // $response->dump();

        $response->assertStatus(201)
            ->assertJsonStructure([
                'access_token'
        ]);
    }

    public function testUserLoginFail()
    {
        $email = $this->faker->safeEmail;
        $pass = Hash::make('123123');
        
        factory(\App\User::class)->create([
            'email' => $email,
            'password' => $pass
        ]);

        $details = [
            'email' => $email,
            'password' => 'asdqwe'
        ];

        $response = $this->post('/api/login', $details, ['Accept' => 'application/json']);

        // $response->dump();

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials.'
        ]);
    }
}
