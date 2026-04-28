<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

test('authenticated user can list failed jobs', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
         ->getJson('/api/failed-jobs')
         ->assertOk()
         ->assertJsonStructure(['data', 'meta']);
});

test('unauthenticated user cannot list failed jobs', function () {
    $this->getJson('/api/failed-jobs')->assertStatus(401);
});

test('failed jobs list returns correct pagination meta', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
                     ->getJson('/api/failed-jobs')
                     ->assertOk();

    expect($response->json('meta'))->toHaveKeys(['current_page', 'per_page', 'total', 'last_page']);
});

test('authenticated user can delete a failed job', function () {
    $user = User::factory()->create();

    $id = DB::table('failed_jobs')->insertGetId([
        'uuid'       => (string) \Illuminate\Support\Str::uuid(),
        'connection' => 'sync',
        'queue'      => 'default',
        'payload'    => '{}',
        'exception'  => 'Test exception',
        'failed_at'  => now(),
    ]);

    $this->actingAs($user, 'sanctum')
         ->deleteJson("/api/failed-jobs/{$id}")
         ->assertOk()
         ->assertJsonFragment(['message' => 'Failed job deleted successfully.']);

    $this->assertDatabaseMissing('failed_jobs', ['id' => $id]);
});
