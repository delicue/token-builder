<?php

use App\Models\TokenPreset;
use App\Models\TokenSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Flux\Flux;

new #[Title('Play Table')] class extends Component {
    public array $cards = [];

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string|max:255')]
    public string $type = '';

    #[Validate('required|array')]
    public array $colors = [];

    #[Validate('required|integer|min:0')]
    public int $power = 1;

    #[Validate('required|integer|min:0')]
    public int $toughness = 1;

    #[Validate('nullable|string|max:255')]
    public string $description = '';

    public array $dieOptions = [4, 6, 8, 10, 12, 20];

    public int $dieSides = 6;

    public int $dieCount = 1;

    /** @var array<int, int> */
    public array $rolls = [];

    public string $sessionName = '';

    public string $sessionDate = '';

    public bool $showPresets = true;

    /**
     * Prepare the form with sensible defaults.
     */
    public function mount(): void
    {
        $this->sessionName = $this->suggestedSessionName();
        $this->sessionDate = now()->toDateString();
    }

    /**
     * The token presets available to add to the canvas.
     */
    public function presets()
    {
        return TokenPreset::orderBy('name')->get();
    }

    /**
     * The current user's saved sessions, most recent first.
     */
    public function sessions()
    {
        return TokenSession::where('user_id', Auth::id())->latest()->get();
    }

    /**
     * A default session name based on how many sessions have been saved.
     */
    public function suggestedSessionName(): string
    {
        return __('Session :number', ['number' => TokenSession::where('user_id', Auth::id())->count() + 1]);
    }

    /**
     * SVG polygon points (in a 0-100 viewBox) for die faces that aren't a
     * square or circle. Regular polygons render far cleaner than clip-path.
     */
    public function diePolygonPoints(): ?string
    {
        return match ($this->dieSides) {
            4 => '50,6 6,94 94,94',
            8 => '50,4 96,50 50,96 4,50',
            10 => '50,4 93.7,35.8 77,87.2 23,87.2 6.3,35.8',
            12 => '50,4 89.8,27 89.8,73 50,96 10.2,73 10.2,27',
            default => null,
        };
    }

    /**
     * The [x, y] pip positions (in a 0-100 viewBox) for a d6 face value.
     *
     * @return array<int, array{0: int, 1: int}>
     */
    public function diePipPositions(int $value): array
    {
        return match ($value) {
            1 => [[50, 50]],
            2 => [[30, 30], [70, 70]],
            3 => [[30, 30], [50, 50], [70, 70]],
            4 => [[30, 30], [70, 30], [30, 70], [70, 70]],
            5 => [[30, 30], [70, 30], [50, 50], [30, 70], [70, 70]],
            6 => [[30, 25], [30, 50], [30, 75], [70, 25], [70, 50], [70, 75]],
            default => [],
        };
    }

    /**
     * Add a preset token to the canvas.
     */
    public function addPreset(int $id): void
    {
        $preset = TokenPreset::findOrFail($id);

        $this->pushCard($preset->name, $preset->type, $preset->power, $preset->toughness, $preset->description, $preset->colors);
    }

    /**
     * Add a custom card built from the form fields.
     */
    public function addCustom(): void
    {
        $this->validate();

        $this->pushCard($this->name, $this->type, $this->power, $this->toughness, $this->description, $this->colors);

        $this->reset(['name', 'type', 'power', 'toughness', 'description', 'colors']);
    }

    /**
     * Roll the selected number and type of dice.
     */
    public function rollDice(): void
    {
        $this->validate([
            'dieSides' => 'required|integer|in:4,6,8,10,12,20',
            'dieCount' => 'required|integer|min:1|max:10',
        ]);

        $this->rolls = collect(range(1, $this->dieCount))
            ->map(fn() => random_int(1, $this->dieSides))
            ->all();
    }

    /**
     * Save the current canvas as a named session.
     */
    public function saveSession(): void
    {
        $this->validate([
            'sessionName' => 'required|string|max:255',
            'sessionDate' => 'required|date',
        ]);

        TokenSession::create([
            'user_id' => Auth::id(),
            'name' => $this->sessionName,
            'played_on' => $this->sessionDate,
            'cards' => $this->cards,
        ]);

        $this->sessionName = $this->suggestedSessionName();
    }

    /**
     * Load a saved session's cards onto the canvas.
     */
    public function loadSession(int $id): void
    {
        $session = TokenSession::where('user_id', Auth::id())->findOrFail($id);

        $this->cards = $session->cards;
    }

    /**
     * Delete a saved session.
     */
    public function deleteSession(int $id): void
    {
        TokenSession::where('user_id', Auth::id())->where('id', $id)->delete();
    }

    /**
     * Remove a card from play.
     */
    public function removeCard(string $id): void
    {
        $this->cards = array_values(array_filter($this->cards, fn(array $card): bool => $card['id'] !== $id));
    }

    /**
     * Toggle whether a card is tapped.
     */
    public function toggleTapped(string $id): void
    {
        $this->updateCard($id, function (array &$card): void {
            $card['tapped'] = !$card['tapped'];
        });
    }

    /**
     * Add a counter that increases power (and toughness too, if linked).
     */
    public function incrementPowerCounter(string $id): void
    {
        $this->updateCard($id, function (array &$card): void {
            $card['power_counters']++;

            if ($card['counters_linked']) {
                $card['toughness_counters'] = $card['power_counters'];
            }
        });
    }

    /**
     * Remove a counter from power (and toughness too, if linked).
     */
    public function decrementPowerCounter(string $id): void
    {
        $this->updateCard($id, function (array &$card): void {
            $card['power_counters']--;

            if ($card['counters_linked']) {
                $card['toughness_counters'] = $card['power_counters'];
            }
        });
    }

    /**
     * Add a counter that increases toughness (and power too, if linked).
     */
    public function incrementToughnessCounter(string $id): void
    {
        $this->updateCard($id, function (array &$card): void {
            $card['toughness_counters']++;

            if ($card['counters_linked']) {
                $card['power_counters'] = $card['toughness_counters'];
            }
        });
    }

    /**
     * Remove a counter from toughness (and power too, if linked).
     */
    public function decrementToughnessCounter(string $id): void
    {
        $this->updateCard($id, function (array &$card): void {
            $card['toughness_counters']--;

            if ($card['counters_linked']) {
                $card['power_counters'] = $card['toughness_counters'];
            }
        });
    }

    /**
     * Toggle whether power/toughness counters move together as one set.
     */
    public function toggleCountersLinked(string $id): void
    {
        $this->updateCard($id, function (array &$card): void {
            $card['counters_linked'] = !$card['counters_linked'];

            if ($card['counters_linked']) {
                $card['toughness_counters'] = $card['power_counters'];
            }
        });
    }

    public function viewDescription(string $id): void
    {
        $this->updateCard($id, function (array &$card): void {
            $card['view_description'] = true;
        });
    }

    public function closeDescription(string $id): void
    {
        $this->updateCard($id, function (array &$card): void {
            $card['view_description'] = false;
        });
    }

    public function toggleShowPresets(): void
    {
        $this->showPresets = !$this->showPresets;
    }

    private function pushCard(string $name, string $type, int $power, int $toughness, ?string $description, array $colors): void
    {
        $this->cards[] = [
            'id' => (string) Str::uuid(),
            'name' => $name,
            'type' => $type,
            'power' => $power,
            'toughness' => $toughness,
            'description' => $description,
            'colors' => $colors,
            'power_counters' => 0,
            'toughness_counters' => 0,
            'counters_linked' => true,
            'tapped' => false,
        ];
        Flux::toast(__('Added :name to play', ['name' => $name]), 'Success');
    }

    private function updateCard(string $id, callable $callback): void
    {
        foreach ($this->cards as &$card) {
            if ($card['id'] === $id) {
                $callback($card);

                return;
            }
        }
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-8">
    <div
        class="flex items-center gap-4 rounded-2xl border border-zinc-200 bg-linear-to-br from-(--theme-surface-from) to-(--theme-surface-to) p-6 dark:border-zinc-700">
        <div
            class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-accent-content text-accent-foreground">
            <flux:icon name="play-circle" class="size-8" />
        </div>
        <div>
            <flux:heading size="xl">{{ __('Play Table') }}</flux:heading>
            <flux:text class="mt-1">
                {{ __('Build tokens, track their stats, roll dice, and save sessions — all in one place.') }}
            </flux:text>
        </div>
    </div>

    <div class="rounded-2xl border border-zinc-200 p-6 shadow-sm dark:border-zinc-700">
        <div class="flex flex-col gap-6">
            <div>
                <flux:heading size="lg">{{ __('Token Builder') }}</flux:heading>
                <flux:text>{{ __('Add preset tokens or build a custom card, then manage them in play.') }}
                </flux:text>
            </div>

            <!-- Presets and custom card form -->
            <div class="flex flex-col gap-6 rounded-xl bg-zinc-50 p-4 dark:bg-zinc-900/60">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 items-center">
                    <div>
                        <flux:heading size="lg">{{ __('Add a token') }}</flux:heading>
                        <flux:text>{{ __('Add a preset token or build a custom card to add to play.') }}
                        </flux:text>
                    </div>
                    <!-- A button to toggle $showPresets, which shows/hides the preset token grid. -->
                    <flux:button
                        wire:click="toggleShowPresets" size="sm"
                        :variant="$this->showPresets ? 'filled' : 'ghost'"
                        class="max-w-1/2 justify-self-center sm:justify-self-end border">
                        {{ $this->showPresets ? __('Hide Presets') : __('Show Presets') }}
                    </flux:button>
                </div>
                <div wire:show="showPresets" x-transition.duration.300ms class="border border-zinc-300 dark:border-zinc-600 rounded-lg">
                    <p
                        class="px-4 py-2 my-1 text-sm text-center md:text-start font-medium text-zinc-600 dark:text-zinc-300">
                        {{ __('Select a preset token to add to play.') }}
                    </p>
                    <hr class="border-zinc-300 dark:border-zinc-600 mx-4 py-px" />
                    <div
                        class="p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-2 max-h-100 overflow-y-auto overflow-x-scroll scrollbar-thin scrollbar-thumb-zinc-300 scrollbar-track-transparent dark:scrollbar-thumb-zinc-700">
                        @foreach ($this->presets() as $preset)
                            <flux:button
                                class="border border-zinc-300 shadow"
                                icon="plus"
                                size="sm"
                                wire:click="addPreset({{ $preset->id }})">
                                    {{ $preset->name }} ({{ $preset->power }}/{{ $preset->toughness }})
                            </flux:button>
                        @endforeach
                    </div>
                    <flux:spacer size="sm" />
                    <hr class="border-zinc-300 dark:border-zinc-600" />
                    <flux:spacer size="sm" />
                </div>


                <form wire:submit="addCustom">
                    <div class="mb-4">
                        <flux:heading size="lg">{{ __('Build a custom card') }}</flux:heading>
                        <flux:text class="text-zinc-500 text-sm">
                            {{ __('Fill out the fields below to add a custom card to play.') }}
                        </flux:text>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 sm:items-end gap-3 text-center mb-2">
                        <flux:input wire:model="name" :label="__('Name')" size="sm" input:class="text-center" />
                        <flux:input wire:model="colors" :label="__('Colors')" size="sm"
                            input:class="text-center" />
                        <flux:input wire:model="type" :label="__('Type')" size="sm" input:class="text-center" />
                    </div>
                    <div class="grid grid-cols-2 gap-3 mb-2">
                        <flux:input wire:model="power" :label="__('Power')" type="number" size="sm"
                            input:class="text-center" />
                        <flux:input wire:model="toughness" :label="__('Toughness')" type="number" size="sm"
                            input:class="text-center" />
                    </div>
                    <flux:textarea class="mb-4" wire:model="description" :label="__('Description')" size="sm"
                        input:class="text-center" />
                    <div class="grid grid-cols-2 gap-3">
                        <flux:button
                            variant="primary"
                            type="submit"
                            size="sm" icon="plus"
                            data-test="add-custom-card-button">
                            {{ __('Add card') }}</flux:button>
                        <flux:button type="reset" size="sm" variant="danger" icon="x-circle"
                            data-test="reset-custom-card-button">
                            {{ __('Clear') }}</flux:button>
                    </div>
                </form>
            </div>

            <!-- Cards in play -->
            <div
                class="grid min-h-40 grid-cols-[repeat(auto-fill,minmax(12rem,1fr))] gap-4 rounded-xl border border-dashed border-zinc-300 p-4 dark:border-zinc-700">
                @forelse ($cards as $card)
                    <div wire:key="{{ $card['id'] }}" data-test="card-{{ $card['id'] }}"
                        class="flex flex-col gap-3 rounded-xl border bg-white p-4 shadow-sm transition-all duration-300 ease-out hover:shadow-md dark:bg-zinc-900 {{ $card['tapped'] ? 'rotate-90 border-amber-300 dark:border-amber-700' : 'border-zinc-200 dark:border-zinc-700' }}">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <flux:heading size="sm" class="truncate">{{ $card['name'] }}</flux:heading>
                                <flux:text size="sm" class="truncate text-zinc-500">{{ $card['type'] }}
                                </flux:text>
                            </div>
                            <flux:button size="xs" variant="ghost" icon="x-mark"
                                wire:click="removeCard('{{ $card['id'] }}')" data-test="remove-card-button"
                                :aria-label="__('Remove')" />
                        </div>

                        <span
                            class="w-fit rounded-lg bg-zinc-100 px-2.5 py-1 font-mono text-lg font-bold dark:bg-zinc-800"
                            data-test="card-stats">
                            {{ $card['power'] + $card['power_counters'] }}/{{ $card['toughness'] + $card['toughness_counters'] }}
                        </span>

                        <div class="mt-auto flex flex-col gap-1 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-500">{{ __('Power') }}</span>
                                <div class="flex items-center gap-1">
                                    <flux:button size="xs" variant="ghost"
                                        wire:click="decrementPowerCounter('{{ $card['id'] }}')">-1</flux:button>
                                    <span data-test="card-power-counters">{{ $card['power_counters'] }}</span>
                                    <flux:button size="xs" variant="ghost"
                                        wire:click="incrementPowerCounter('{{ $card['id'] }}')">+1</flux:button>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-zinc-500">{{ __('Toughness') }}</span>
                                <div class="flex items-center gap-1">
                                    <flux:button size="xs" variant="ghost"
                                        wire:click="decrementToughnessCounter('{{ $card['id'] }}')"
                                        :disabled="$card['counters_linked']">-1</flux:button>
                                    <span data-test="card-toughness-counters">{{ $card['toughness_counters'] }}</span>
                                    <flux:button size="xs" variant="ghost"
                                        wire:click="incrementToughnessCounter('{{ $card['id'] }}')"
                                        :disabled="$card['counters_linked']">+1</flux:button>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-1">
                            <flux:button size="xs" wire:click="toggleCountersLinked('{{ $card['id'] }}')"
                                data-test="toggle-linked-button">
                                {{ $card['counters_linked'] ? __('Unlink counters') : __('Link counters') }}
                            </flux:button>

                            <flux:button size="xs" wire:click="toggleTapped('{{ $card['id'] }}')"
                                data-test="toggle-tapped-button">
                                {{ $card['tapped'] ? __('Untap') : __('Tap') }}
                            </flux:button>

                            <flux:modal.trigger name="view-description">
                                <flux:button>View Description</flux:button>
                            </flux:modal.trigger>

                            <flux:modal name="view-description" class="md:w-96">
                                <div class="space-y-6">
                                    <div>
                                        <flux:heading size="lg">View Description</flux:heading>
                                        <flux:text class="mt-2">{{ $card['description'] }}</flux:text>
                                    </div>
                                </div>
                            </flux:modal>
                        </div>
                    </div>
                @empty
                    <div
                        class="col-span-full flex flex-col items-center justify-center gap-2 self-center py-6 text-center text-zinc-400">
                        <flux:icon name="rectangle-stack" class="size-8" />
                        <flux:text class="text-zinc-500">{{ __('No cards in play yet — add one above.') }}
                        </flux:text>
                    </div>
                @endforelse
            </div>

            <div class="flex flex-col gap-4 rounded-xl bg-zinc-50 p-4 dark:bg-zinc-900/60">
                <flux:heading size="lg">{{ __('Dice') }}</flux:heading>

                <form wire:submit="rollDice" class="flex flex-wrap items-end gap-3">
                    <flux:select wire:model="dieSides" :label="__('Die type')" size="sm">
                        @foreach ($dieOptions as $sides)
                            <flux:select.option value="{{ $sides }}">d{{ $sides }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:input wire:model="dieCount" :label="__('How many')" type="number" min="1"
                        max="10" size="sm" class="w-20" />

                    <flux:button type="submit" size="sm" icon="arrow-path" data-test="roll-dice-button">
                        {{ __('Roll') }}</flux:button>

                    @if (count($rolls))
                        <flux:text class="ml-auto">{{ __('Total') }}: <span class="font-mono font-bold"
                                data-test="dice-total">{{ array_sum($rolls) }}</span></flux:text>
                    @endif
                </form>

                @if (count($rolls))
                    <div class="flex flex-wrap gap-3" data-test="dice-results">
                        @foreach ($rolls as $roll)
                            <div
                                class="flex size-16 shrink-0 items-center justify-center border-2 rounded-2xl border-zinc-400 bg-white p-2 dark:border-zinc-500 dark:bg-zinc-900">
                                <span class="font-mono text-lg font-bold">{{ $roll }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="flex flex-col gap-4 rounded-xl">
                <flux:heading size="lg">{{ __('Sessions') }}</flux:heading>

                <form wire:submit="saveSession" class="flex flex-wrap items-end gap-3">
                    <flux:input wire:model="sessionName" :label="__('Session name')" size="sm" />
                    <flux:input wire:model="sessionDate" :label="__('Date')" type="date" size="sm" />
                    <flux:button type="submit" size="sm" icon="bookmark" data-test="save-session-button">
                        {{ __('Save session') }}</flux:button>
                </form>

                @if ($this->sessions()->isNotEmpty())
                    <ul class="flex flex-col gap-2">
                        @foreach ($this->sessions() as $session)
                            <li class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700"
                                data-test="session-{{ $session->id }}">
                                <div>
                                    <span class="font-medium">{{ $session->name }}</span>
                                    <span class="text-zinc-500">—
                                        {{ $session->played_on->format('M j, Y') }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <flux:button size="xs" variant="ghost"
                                        wire:click="loadSession({{ $session->id }})"
                                        data-test="load-session-button">
                                        {{ __('Load') }}
                                    </flux:button>
                                    <flux:button size="xs" variant="ghost" icon="trash"
                                        wire:click="deleteSession({{ $session->id }})"
                                        data-test="delete-session-button" :aria-label="__('Delete')" />
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <flux:text class="text-zinc-500">{{ __('No saved sessions yet.') }}</flux:text>
                @endif
            </div>
        </div>
    </div>
</div>
