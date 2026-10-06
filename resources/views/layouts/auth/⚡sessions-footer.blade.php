<?php

namespace App\Livewire\Dashboard;

use App\Models\TokenSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Flux\Flux;

new class extends Component
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

?>

<footer
    data-session-footer
    role="region"
    aria-labelledby="dashboard-sessions-title"
    class="dashboard-session-footer mt-auto w-full rounded-lg border border-zinc-300 bg-linear-to-r from-(--theme-surface-from) to-(--theme-surface-to) p-4 text-zinc-900 shadow-sm dark:border-zinc-700 dark:text-zinc-100 sm:p-5"
>
    <div class="mx-auto max-w-screen-2xl px-4 sm:px-6">
        <div class="flex min-h-12 items-center justify-between gap-4">
            <flux:heading id="dashboard-sessions-title" size="sm">{{ __('Sessions') }}</flux:heading>
            <flux:button
                type="button"
                size="sm"
                variant="ghost"
                :icon="$expanded ? 'chevron-down' : 'chevron-up'"
                :aria-label="$expanded ? __('Collapse sessions') : __('Expand sessions')"
                aria-controls="dashboard-session-content"
                :aria-expanded="$expanded"
                wire:click="toggleExpanded"
            />
        </div>

        @if ($expanded)
            <div id="dashboard-session-content" class="grid gap-5 border-t border-zinc-300 py-4 dark:border-zinc-700 lg:grid-cols-[minmax(16rem,0.8fr)_minmax(0,1.2fr)]">
                <form class="grid content-start gap-3 sm:grid-cols-[minmax(0,1fr)_auto] lg:grid-cols-1 xl:grid-cols-[minmax(0,1fr)_auto]">
                    <flux:input wire:model="sessionName" :label="__('Session name')" size="sm" />
                    <flux:input wire:model="playedOn" :label="__('Played on')" type="date" size="sm" />
                    <flux:button type="button" size="sm" icon="bookmark" x-on:click="$dispatch('token-builder-save-session')" class="sm:col-span-2 lg:col-span-1 xl:col-span-2">
                        {{ __('Save current work') }}
                    </flux:button>
                </form>

                <div class="max-h-[30vh] overflow-y-auto">
                    @if ($this->sessions()->isNotEmpty())
                        <ul class="grid gap-2 sm:grid-cols-2">
                            @foreach ($this->sessions() as $session)
                                <li class="flex min-w-0 items-center justify-between gap-3 rounded-lg border border-zinc-200 px-3 py-2 dark:border-zinc-700" data-test="session-{{ $session->id }}">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium">{{ $session->name }}</p>
                                        <time class="text-xs text-zinc-600 dark:text-zinc-400" datetime="{{ $session->updated_at->toIso8601String() }}">
                                            {{ $session->updated_at->format('M j, Y g:i A') }}
                                        </time>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-1">
                                        <flux:button type="button" size="xs" variant="ghost" icon="arrow-down-tray" wire:click="loadSession({{ $session->id }})" :aria-label="__('Load :name', ['name' => $session->name])" />
                                        <flux:button type="button" size="xs" variant="ghost" icon="trash" wire:click="deleteSession({{ $session->id }})" :aria-label="__('Delete :name', ['name' => $session->name])" />
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <flux:text class="py-2 text-sm text-zinc-600 dark:text-zinc-400">{{ __('No saved sessions yet.') }}</flux:text>
                    @endif
                </div>
            </div>
        @else
            <div class="flex min-h-9 items-center gap-5 overflow-x-auto py-2 text-zinc-900 dark:text-zinc-100" aria-label="{{ __('Saved sessions') }}">
                @forelse ($this->sessions() as $session)
                    <div class="flex shrink-0 items-baseline gap-2 text-sm" data-test="collapsed-session-{{ $session->id }}">
                        <span class="max-w-48 truncate font-medium">{{ $session->name }}</span>
                        <time class="text-xs text-zinc-600 dark:text-zinc-400" datetime="{{ $session->updated_at->toIso8601String() }}">
                            {{ $session->updated_at->format('M j, Y g:i A') }}
                        </time>
                    </div>
                @empty
                    <span class="text-xs text-zinc-600 dark:text-zinc-400">{{ __('No saved sessions yet.') }}</span>
                @endforelse
            </div>
        @endif
    </div>
</footer>
