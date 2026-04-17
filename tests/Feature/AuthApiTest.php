<?php

use App\Models\User;

test('user can register', function () {
    $response = $this->postJson('/api/auth/register', [
        'name'                  => 'Test User',
        'email'                 => 'test@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201)
             ->assertJsonStructure(['user', 'token']);

    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
});

test('register fails with duplicate email', function () {
    User::factory()->create(['email' => 'dupe@example.com']);

    $this->postJson('/api/auth/register', [
        'name'                  => 'Another',
        'email'                 => 'dupe@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ])->assertStatus(422);
});

test('user can login', function () {
    User::factory()->create([
        'email'    => 'login@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email'    => 'login@example.com',
        'password' => 'password123',
    ]);

    $response->assertOk()
             ->assertJsonStructure(['user', 'token']);
});

test('login fails with wrong credentials', function () {
    User::factory()->create(['email' => 'wrong@example.com']);

    $this->postJson('/api/auth/login', [
        'email'    => 'wrong@example.com',
        'password' => 'bad_password',
    ])->assertStatus(422);
});

test('authenticated user can fetch profile', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
         ->getJson('/api/auth/me')
         ->assertOk()
         ->assertJsonFragment(['email' => $user->email]);
});

test('unauthenticated request is rejected', function () {
    $this->getJson('/api/auth/me')->assertStatus(401);
    $this->getJson('/api/users')->assertStatus(401);
});

test('user can logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
         ->postJson('/api/auth/logout')
         ->assertOk();
});
