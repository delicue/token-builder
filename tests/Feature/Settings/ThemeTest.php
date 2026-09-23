<?php

use App\Models\User;
use Livewire\Livewire;

test('theme settings page is displayed', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('theme.edit'))->assertOk();
});

test('the default theme is violet', function () {
    $user = User::factory()->create();

    expect($user->theme)->toBe('violet');
});

test('a user can select a different theme', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test('pages::settings.theme')
        ->call('selectTheme', 'emerald');

    expect($user->fresh()->theme)->toBe('emerald');
});
