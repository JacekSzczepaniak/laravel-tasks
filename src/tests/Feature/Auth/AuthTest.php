<?php
// src/tests/Feature/Auth/AuthTest.php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\{get, post, actingAs};

uses(RefreshDatabase::class)->group('auth');

/**
 * --- REJESTRACJA ---
 */

test('registration screen can be rendered', function () {
    get('/register')->assertOk();
});

test('user can register', function () {
    $response = post('/register', [
        'name' => 'Jacek Test',
        'email' => 'jacek@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    // Użytkownik powinien zostać zalogowany i przekierowany do dashboardu
    $this->assertAuthenticated();
    $response->assertRedirect('/dashboard');
});

test('cannot register with duplicate email', function () {
    User::factory()->create(['email' => 'dup@example.com']);

    $response = post('/register', [
        'name' => 'Dup User',
        'email' => 'dup@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors(['email']);
    $this->assertGuest();
});

/**
 * --- LOGOWANIE ---
 */

test('login screen can be rendered', function () {
    get('/login')->assertOk();
});

test('user can login with correct credentials', function () {
    $user = User::factory()->create([
        'password' => bcrypt('secret123'),
    ]);

    $response = post('/login', [
        'email' => $user->email,
        'password' => 'secret123',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect('/dashboard');
});

test('user cannot login with wrong password', function () {
    $user = User::factory()->create([
        'password' => bcrypt('secret123'),
    ]);

    $response = post('/login', [
        'email' => $user->email,
        'password' => 'bad-password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors(); // ogólna walidacja/credentials
});

/**
 * --- WYLOGOWANIE ---
 */

test('authenticated user can logout', function () {
    $user = User::factory()->create();

    actingAs($user);
    $response = post('/logout');

    $this->assertGuest();
    // Breeze zwykle przekierowuje na /
    $response->assertRedirect('/');
});

/**
 * --- OCHRONA ROUTÓW ---
 */

test('guest is redirected from dashboard to login', function () {
    get('/dashboard')->assertRedirect('/login');
});

test('authenticated user sees dashboard', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get('/dashboard')
        ->assertOk();
});
