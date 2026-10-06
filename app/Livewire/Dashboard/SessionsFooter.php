<?php

namespace App\Livewire\Dashboard;

use App\Models\TokenSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Flux\Flux;

class SessionsFooter extends Component
{
    public string $sessionName = '';

    public string $playedOn = '';

    public bool $expanded = false;

    public function mount(): void
    {
        $this->sessionName = $this->suggestedSessionName();
        $this->playedOn = now()->toDateString();
    }

    public function sessions()
    {
        return TokenSession::where('user_id', Auth::id())->latest('updated_at')->get();
    }

    public function toggleExpanded(): void
    {
        $this->expanded = ! $this->expanded;
    }

    public function saveSession(array $drafts): void
    {
        $cards = $drafts['pages::dashboard.play-table']['cards'] ?? [];
        $counters = $drafts['pages::dashboard.counters']['counters'] ?? [];

        $session = Validator::make([
            'name' => $this->sessionName,
            'played_on' => $this->playedOn,
            'cards' => $cards,
            'counters' => $counters,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'played_on' => ['required', 'date'],
            'cards' => ['array', 'max:100'],
            'cards.*.id' => ['required', 'string', 'max:64'],
            'cards.*.name' => ['required', 'string', 'max:255'],
            'cards.*.type' => ['required', 'string', 'max:255'],
            'cards.*.power' => ['required', 'integer', 'between:0,1000000'],
            'cards.*.toughness' => ['required', 'integer', 'between:0,1000000'],
            'cards.*.description' => ['nullable', 'string', 'max:255'],
            'cards.*.colors' => ['nullable', 'array'],
            'cards.*.power_counters' => ['nullable', 'integer', 'between:-1000000,1000000'],
            'cards.*.toughness_counters' => ['nullable', 'integer', 'between:-1000000,1000000'],
            'cards.*.counters_linked' => ['nullable', 'boolean'],
            'cards.*.tapped' => ['nullable', 'boolean'],
            'counters' => ['array', 'max:64'],
            'counters.*' => ['integer', 'between:-1000000,1000000'],
        ])->validate();

        TokenSession::create([
            'user_id' => Auth::id(),
            'name' => $session['name'],
            'played_on' => $session['played_on'],
            'cards' => $session['cards'] ?? [],
            'counters' => $session['counters'] ?? [],
        ]);

        $this->sessionName = $this->suggestedSessionName();
        Flux::toast(__('Session saved'), 'Success');
    }

    public function loadSession(int $id): void
    {
        $session = TokenSession::where('user_id', Auth::id())->findOrFail($id);

        $this->dispatch(
            'dashboard-session-loaded',
            cards: $session->cards ?? [],
            counters: $session->counters ?? [],
        );
    }

    public function deleteSession(int $id): void
    {
        TokenSession::where('user_id', Auth::id())->whereKey($id)->delete();
    }

    private function suggestedSessionName(): string
    {
        return __('Session :number', [
            'number' => TokenSession::where('user_id', Auth::id())->count() + 1,
        ]);
    }
}
