<?php

use App\Models\User;

use function Pest\Laravel\postJson;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('user can register', function () {
    $password = 'password123';
    $data = [
        'name' => fake()->name,
        'email' => fake()->unique()->safeEmail,
        'password' => $password,
        'password_confirmation' => $password,
        'device_name' => 'test-device',
    ];

    postJson('/api/register', $data)
        ->assertStatus(201)
        ->assertJsonStructure([
            'token',
            'user' => [
                'id',
                'name',
                'email',
                'created_at',
                'updated_at',
            ],
        ]);
});

test('user cannot register with existing email', function () {
    $existingUser = User::factory()->create();

    $data = [
        'name' => fake()->name,
        'email' => $existingUser->email,
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'device_name' => 'test-device',
    ];

    postJson('/api/register', $data)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('user can login', function () {
    $password = 'password123';
    $user = User::factory()->create([
        'password' => bcrypt($password),
    ]);

    postJson('/api/login', [
        'email' => $user->email,
        'password' => $password,
        'device_name' => 'test-device',
    ])
        ->assertOk()
        ->assertJsonStructure([
            'token',
            'user' => [
                'id',
                'name',
                'email',
                'created_at',
                'updated_at',
            ],
        ]);
});

test('user cannot login with invalid credentials', function () {
    $user = User::factory()->create();

    postJson('/api/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
        'device_name' => 'test-device',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('user can logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-device')->plainTextToken;

    postJson('/api/logout', [], [
        'Authorization' => "Bearer {$token}",
    ])
        ->assertOk()
        ->assertJson(['message' => 'Logged out successfully']);

    expect(db()->table('personal_access_tokens')->count())->toBe(0);
});
