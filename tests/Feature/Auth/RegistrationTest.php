<?php

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertStatus(200);
});

test('new users can register', function () {
    // Ensure the trainer role exists for the test
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'trainer']);

    $response = $this->post(route('register.store'), [
        'first_name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});
