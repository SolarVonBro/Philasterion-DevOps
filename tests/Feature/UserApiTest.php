<?php

use App\Models\User;

test('authenticated user can list users', function () {
    User::factory()->count(3)->create();
    $actor = User::factory()->create();

    $response = $this->actingAs($actor, 'sanctum')
                     ->getJson('/api/users');

    $response->assertOk()
             ->assertJsonStructure(['data', 'meta']);
});

test('authenticated user can create a user', function () {
    $actor = User::factory()->create();

    $response = $this->actingAs($actor, 'sanctum')
                     ->postJson('/api/users', [
                         'name'     => 'New User',
                         'email'    => 'new@example.com',
                         'password' => 'password123',
                     ]);

    $response->assertStatus(201)
             ->assertJsonFragment(['email' => 'new@example.com']);

    $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
});

test('authenticated user can update a user', function () {
    $actor  = User::factory()->create();
    $target = User::factory()->create();

    $this->actingAs($actor, 'sanctum')
         ->putJson("/api/users/{$target->id}", ['name' => 'Updated Name'])
         ->assertOk()
         ->assertJsonFragment(['name' => 'Updated Name']);
});

test('authenticated user can delete a user', function () {
    $actor  = User::factory()->create();
    $target = User::factory()->create();

    $this->actingAs($actor, 'sanctum')
         ->deleteJson("/api/users/{$target->id}")
         ->assertOk();

    $this->assertDatabaseMissing('users', ['id' => $target->id]);
});

test('create user fails with invalid data', function () {
    $actor = User::factory()->create();

    $this->actingAs($actor, 'sanctum')
         ->postJson('/api/users', ['name' => '', 'email' => 'not-an-email'])
         ->assertStatus(422);
});
