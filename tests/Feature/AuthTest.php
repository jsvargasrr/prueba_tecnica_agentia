<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('registers and logs in a user', function () {
    // registro
    $res = $this->postJson('/api/auth/register', [
        'name' => 'Sebas',
        'email' => 'sebas@example.com',
        'password' => 'secret123',
    ]);

    $res->assertCreated()->assertJsonStructure(['token']);

    // login
    $res2 = $this->postJson('/api/auth/login', [
        'email' => 'sebas@example.com',
        'password' => 'secret123',
    ]);
    $res2->assertOk()->assertJsonStructure(['token']);

    $token = $res2->json('token');

   
    $me = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/auth/me');
    $me->assertOk()->assertJsonPath('email', 'sebas@example.com');
});
