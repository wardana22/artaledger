<?php

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;

test('authenticated superadmin can visit the dashboard page', function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('Super Admin');

    $response = $this->actingAs($user)->get(route('dashboard'));
    $response->assertStatus(200);
});
