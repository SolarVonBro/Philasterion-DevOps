<?php

use App\Models\DiaryEntry;
use App\Models\User;

test('authenticated user can list diary entries', function () {
    $user = User::factory()->create();
    DiaryEntry::factory()->count(3)->create(['user_id' => $user->id]);

    $this->actingAs($user, 'sanctum')
         ->getJson('/api/diary')
         ->assertOk()
         ->assertJsonStructure(['data', 'meta']);
});

test('unauthenticated user cannot list diary entries', function () {
    $this->getJson('/api/diary')->assertStatus(401);
});

test('user only sees their own diary entries', function () {
    $user  = User::factory()->create();
    $other = User::factory()->create();
    DiaryEntry::factory()->count(2)->create(['user_id' => $user->id]);
    DiaryEntry::factory()->count(3)->create(['user_id' => $other->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/diary');

    $response->assertOk();
    expect($response->json('meta.total'))->toBe(2);
});

test('authenticated user can create a diary entry', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
         ->postJson('/api/diary', [
             'recorded_at' => '2024-06-01',
             'mood'        => 4,
             'energy'      => 7,
             'sleep_hours' => 8.0,
             'notes'       => 'Good day',
         ])
         ->assertStatus(201)
         ->assertJsonFragment(['mood' => 4]);

    $this->assertDatabaseHas('diary_entries', [
        'user_id' => $user->id,
        'mood'    => 4,
    ]);
});

test('create diary entry fails with invalid data', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
         ->postJson('/api/diary', [
             'mood'   => 10,
             'energy' => -1,
         ])
         ->assertStatus(422);
});

test('authenticated user can show a diary entry', function () {
    $user  = User::factory()->create();
    $entry = DiaryEntry::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user, 'sanctum')
         ->getJson("/api/diary/{$entry->id}")
         ->assertOk()
         ->assertJsonFragment(['mood' => $entry->mood]);
});

test('user cannot view another users diary entry', function () {
    $user  = User::factory()->create();
    $other = User::factory()->create();
    $entry = DiaryEntry::factory()->create(['user_id' => $other->id]);

    $this->actingAs($user, 'sanctum')
         ->getJson("/api/diary/{$entry->id}")
         ->assertStatus(403);
});

test('authenticated user can update a diary entry', function () {
    $user  = User::factory()->create();
    $entry = DiaryEntry::factory()->create(['user_id' => $user->id, 'mood' => 2]);

    $this->actingAs($user, 'sanctum')
         ->putJson("/api/diary/{$entry->id}", ['mood' => 5])
         ->assertOk()
         ->assertJsonFragment(['mood' => 5]);
});

test('user cannot update another users diary entry', function () {
    $user  = User::factory()->create();
    $other = User::factory()->create();
    $entry = DiaryEntry::factory()->create(['user_id' => $other->id]);

    $this->actingAs($user, 'sanctum')
         ->putJson("/api/diary/{$entry->id}", ['mood' => 5])
         ->assertStatus(403);
});

test('authenticated user can delete a diary entry', function () {
    $user  = User::factory()->create();
    $entry = DiaryEntry::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user, 'sanctum')
         ->deleteJson("/api/diary/{$entry->id}")
         ->assertOk()
         ->assertJsonFragment(['message' => 'Entry deleted.']);

    $this->assertDatabaseMissing('diary_entries', ['id' => $entry->id]);
});

test('user cannot delete another users diary entry', function () {
    $user  = User::factory()->create();
    $other = User::factory()->create();
    $entry = DiaryEntry::factory()->create(['user_id' => $other->id]);

    $this->actingAs($user, 'sanctum')
         ->deleteJson("/api/diary/{$entry->id}")
         ->assertStatus(403);
});
