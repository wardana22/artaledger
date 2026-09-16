<?php

use Laravel\Fortify\Features;

test('public registration is disabled and returns 404', function () {
    expect(Features::enabled(Features::registration()))->toBeFalse();

    $response = $this->get('/register');
    $response->assertNotFound();
});

test('login page does not display sign up link when registration is disabled', function () {
    $response = $this->get(route('login'));
    $response->assertOk();
    $response->assertDontSee('Sign up');
    $response->assertSee('Sistem Terproteksi — Registrasi akun baru hanya melalui Administrator.');
});
