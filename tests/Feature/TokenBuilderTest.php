<?php

use App\Models\TokenPreset;
use App\Models\TokenSession;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->soldier = TokenPreset::create(['name' => 'Soldier', 'type' => 'Creature — Soldier', 'power' => 1, 'toughness' => 1]);
    $this->zombie = TokenPreset::create(['name' => 'Zombie', 'type' => 'Creature — Zombie', 'power' => 2, 'toughness' => 2]);
});

test('preset tokens can be added to the canvas', function () {
    $this->actingAs(User::factory()->create());

    $component = Livewire::test('pages::play-table')
        ->call('addPreset', $this->soldier->id);

    expect($component->get('cards'))->toHaveCount(1);
    expect($component->get('cards')[0])
        ->name->toBe('Soldier')
        ->type->toBe('Creature — Soldier')
        ->power->toBe(1)
        ->toughness->toBe(1)
        ->power_counters->toBe(0)
        ->toughness_counters->toBe(0)
        ->counters_linked->toBeTrue()
        ->tapped->toBeFalse();
});

test('a custom card can be added from the form', function () {
    $this->actingAs(User::factory()->create());

    $component = Livewire::test('pages::play-table')
        ->set('name', 'Dragon')
        ->set('type', 'Creature — Dragon')
        ->set('power', 5)
        ->set('toughness', 5)
        ->call('addCustom');

    $component->assertHasNoErrors();

    expect($component->get('cards'))->toHaveCount(1);
    expect($component->get('cards')[0])
        ->name->toBe('Dragon')
        ->power->toBe(5)
        ->toughness->toBe(5);
    expect($component->get('name'))->toBe('');
});

test('a custom card requires its fields', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test('pages::play-table')
        ->set('name', '')
        ->call('addCustom')
        ->assertHasErrors(['name']);
});

test('a card can be tapped and untapped', function () {
    $this->actingAs(User::factory()->create());

    $component = Livewire::test('pages::play-table')
        ->call('addPreset', $this->soldier->id);

    $id = $component->get('cards')[0]['id'];

    $component->call('toggleTapped', $id);
    expect($component->get('cards')[0]['tapped'])->toBeTrue();

    $component->call('toggleTapped', $id);
    expect($component->get('cards')[0]['tapped'])->toBeFalse();
});

test('linked counters adjust both power and toughness together', function () {
    $this->actingAs(User::factory()->create());

    $component = Livewire::test('pages::play-table')
        ->call('addPreset', $this->soldier->id);

    $id = $component->get('cards')[0]['id'];

    $component->call('incrementPowerCounter', $id)->call('incrementPowerCounter', $id);
    expect($component->get('cards')[0])
        ->power_counters->toBe(2)
        ->toughness_counters->toBe(2);

    $component->call('decrementToughnessCounter', $id);
    expect($component->get('cards')[0])
        ->power_counters->toBe(1)
        ->toughness_counters->toBe(1);
});

test('counters can be unlinked to adjust power and toughness independently', function () {
    $this->actingAs(User::factory()->create());

    $component = Livewire::test('pages::play-table')
        ->call('addPreset', $this->soldier->id);

    $id = $component->get('cards')[0]['id'];

    $component->call('toggleCountersLinked', $id);
    expect($component->get('cards')[0]['counters_linked'])->toBeFalse();

    $component->call('incrementPowerCounter', $id)->call('incrementPowerCounter', $id);
    $component->call('decrementToughnessCounter', $id);

    expect($component->get('cards')[0])
        ->power_counters->toBe(2)
        ->toughness_counters->toBe(-1);
});

test('relinking counters syncs toughness counters to power counters', function () {
    $this->actingAs(User::factory()->create());

    $component = Livewire::test('pages::play-table')
        ->call('addPreset', $this->soldier->id);

    $id = $component->get('cards')[0]['id'];

    $component->call('toggleCountersLinked', $id);
    $component->call('incrementPowerCounter', $id)->call('incrementPowerCounter', $id);
    $component->call('decrementToughnessCounter', $id);

    $component->call('toggleCountersLinked', $id);

    expect($component->get('cards')[0])
        ->counters_linked->toBeTrue()
        ->power_counters->toBe(2)
        ->toughness_counters->toBe(2);
});

test('a card can be removed from play', function () {
    $this->actingAs(User::factory()->create());

    $component = Livewire::test('pages::play-table')
        ->call('addPreset', $this->soldier->id)
        ->call('addPreset', $this->zombie->id);

    $id = $component->get('cards')[0]['id'];

    $component->call('removeCard', $id);

    expect($component->get('cards'))->toHaveCount(1);
    expect($component->get('cards')[0]['name'])->toBe('Zombie');
});

test('rolling dice fills the results with values within range', function () {
    $this->actingAs(User::factory()->create());

    $component = Livewire::test('pages::play-table')
        ->set('dieSides', 20)
        ->set('dieCount', 3)
        ->call('rollDice');

    $component->assertHasNoErrors();

    $rolls = $component->get('rolls');

    expect($rolls)->toHaveCount(3);

    foreach ($rolls as $roll) {
        expect($roll)->toBeGreaterThanOrEqual(1)->toBeLessThanOrEqual(20);
    }
});

test('rolling dice requires a valid count', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test('pages::play-table')
        ->set('dieCount', 0)
        ->call('rollDice')
        ->assertHasErrors(['dieCount']);
});

test('a session can be saved with the suggested name and today\'s date', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $component = Livewire::test('pages::play-table')
        ->call('addPreset', $this->soldier->id);

    expect($component->get('sessionName'))->toBe('Session 1');

    $component->call('saveSession');

    expect(TokenSession::where('user_id', $user->id)->count())->toBe(1);

    $session = TokenSession::where('user_id', $user->id)->first();
    expect($session->name)->toBe('Session 1');
    expect($session->played_on->toDateString())->toBe(now()->toDateString());
    expect($session->cards)->toHaveCount(1);
});

test('the suggested session name increments with each saved session', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    TokenSession::create([
        'user_id' => $user->id,
        'name' => 'Session 1',
        'played_on' => now(),
        'cards' => [],
    ]);

    $component = Livewire::test('pages::play-table');

    expect($component->get('sessionName'))->toBe('Session 2');
});

test('a saved session can be loaded back onto the canvas', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $session = TokenSession::create([
        'user_id' => $user->id,
        'name' => 'Saved Game',
        'played_on' => now(),
        'cards' => [['id' => 'abc', 'name' => 'Zombie', 'type' => 'Creature — Zombie', 'power' => 2, 'toughness' => 2, 'power_counters' => 0, 'toughness_counters' => 0, 'counters_linked' => true, 'tapped' => false]],
    ]);

    $component = Livewire::test('pages::play-table')
        ->call('loadSession', $session->id);

    expect($component->get('cards'))->toHaveCount(1);
    expect($component->get('cards')[0]['name'])->toBe('Zombie');
});

test('a saved session can be deleted', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $session = TokenSession::create([
        'user_id' => $user->id,
        'name' => 'Saved Game',
        'played_on' => now(),
        'cards' => [],
    ]);

    Livewire::test('pages::play-table')
        ->call('deleteSession', $session->id);

    expect(TokenSession::where('user_id', $user->id)->count())->toBe(0);
});

test('a user cannot load or delete another user\'s session', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();

    $session = TokenSession::create([
        'user_id' => $owner->id,
        'name' => 'Private Session',
        'played_on' => now(),
        'cards' => [],
    ]);

    $this->actingAs($intruder);

    expect(fn () => Livewire::test('pages::play-table')->call('loadSession', $session->id))
        ->toThrow(Illuminate\Database\Eloquent\ModelNotFoundException::class);

    Livewire::test('pages::play-table')->call('deleteSession', $session->id);

    expect(TokenSession::find($session->id))->not->toBeNull();
});
