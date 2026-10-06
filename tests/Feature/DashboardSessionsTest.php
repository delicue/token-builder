<?php

use App\Livewire\Dashboard\SessionsFooter;
use App\Models\TokenSession;
use App\Models\User;
use Livewire\Livewire;

function dashboardSessionCard(): array
{
    return [
        'id' => 'card-1',
        'name' => 'Scout',
        'type' => 'Creature',
        'power' => 2,
        'toughness' => 3,
        'description' => '',
        'colors' => [],
        'power_counters' => 0,
        'toughness_counters' => 0,
        'counters_linked' => true,
        'tapped' => false,
    ];
}

test('a saved session stores current play table cards and counters', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $drafts = [
        'pages::dashboard.play-table' => ['cards' => [dashboardSessionCard()]],
        'pages::dashboard.counters' => ['counters' => [4, 9]],
    ];

    Livewire::test(SessionsFooter::class)
        ->set('sessionName', 'Friday game')
        ->call('saveSession', $drafts)
        ->assertHasNoErrors();

    $session = TokenSession::where('user_id', $user->id)->sole();

    expect($session->cards)->toHaveCount(1)
        ->and($session->counters)->toBe([4, 9]);
});

test('loading a saved session dispatches both cards and counters', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $cards = [dashboardSessionCard()];

    $session = TokenSession::create([
        'user_id' => $user->id,
        'name' => 'Friday game',
        'played_on' => now(),
        'cards' => $cards,
        'counters' => [2, 6],
    ]);

    Livewire::test(SessionsFooter::class)
        ->call('loadSession', $session->id)
        ->assertDispatched('dashboard-session-loaded', cards: $cards, counters: [2, 6]);
});

test('counter page restores counters from a loaded session', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test('pages::dashboard.counters')
        ->dispatch('dashboard-session-loaded', cards: [], counters: [3, 8])
        ->assertSet('counters', [3, 8]);
});

test('play table restores cards from a loaded session', function () {
    $this->actingAs(User::factory()->create());
    $cards = [dashboardSessionCard()];

    Livewire::test('pages::dashboard.play-table')
        ->dispatch('dashboard-session-loaded', cards: $cards, counters: [])
        ->assertSet('cards', $cards);
});

test('the shared sessions footer is present on every dashboard page', function () {
    $this->actingAs(User::factory()->create());

    foreach ([
        route('pages.dashboard.index'),
        route('pages.dashboard.counters'),
        route('pages.dashboard.play-table'),
    ] as $url) {
        $this->get($url)
            ->assertOk()
            ->assertSee('data-session-footer', false)
            ->assertSee(__('Sessions'));
    }
});

test('users cannot load another user’s saved session', function () {
    $owner = User::factory()->create();
    $session = TokenSession::create([
        'user_id' => $owner->id,
        'name' => 'Private game',
        'played_on' => now(),
        'cards' => [],
        'counters' => [],
    ]);

    $this->actingAs(User::factory()->create());

    expect(fn () => Livewire::test(SessionsFooter::class)->call('loadSession', $session->id))
        ->toThrow(Illuminate\Database\Eloquent\ModelNotFoundException::class);
});
