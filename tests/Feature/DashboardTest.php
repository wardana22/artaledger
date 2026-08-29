<?php

use App\Models\User;

test('dashboard route redirects to journals index', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('accounting.journals.index'));
});
