<?php

use Livewire\Component;
use App\Models\TokenPreset;
use App\Models\TokenSession;
use Livewire\Attributes\Title;

new #[Title('Dashboard - Home')] class extends Component {

    /**
     * The current user's saved sessions, most recent first.
     */
    public function sessionCount()
    {
        return TokenSession::where('user_id', Auth::id())->count();
    }
};
?>

<div class="flex h-full w-full flex-1 flex-col gap-8">
    <div
        class="flex items-center gap-4 rounded-2xl border border-zinc-200 bg-linear-to-br from-(--theme-surface-from) to-(--theme-surface-to) p-6 dark:border-zinc-700">
        <div
            class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-accent-content text-accent-foreground">
            <flux:icon name="sparkles" class="size-6" />
        </div>
        <div>
            <flux:heading size="xl">{{ __('Dashboard') }}</flux:heading>
            <flux:text class="mt-1">
                {{ __('Build tokens, track their stats, roll dice, and save sessions — all in one place.') }}
            </flux:text>
        </div>
    </div>

    <!-- Dashboard: Widgets -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-16">
        {{-- <div
            class="flex cursor-pointer items-center gap-4 rounded-2xl border border-zinc-200 bg-linear-to-br from-(--theme-surface-from) to-(--theme-surface-to) p-6 dark:border-zinc-700">
            <div
                class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-accent-content text-accent-foreground">
                <flux:icon name="squares-2x2" class="size-6" />
            </div>
            <div>
                <flux:heading size="xl">{{ TokenPreset::count() }}</flux:heading>
                <flux:text class="mt-1">{{ __('Token Presets') }}</flux:text>
            </div>
        </div> --}}
        <div
            class="flex items-center gap-4 rounded-2xl border border-zinc-200 bg-linear-to-br from-(--theme-surface-from) to-(--theme-surface-to) p-6 dark:border-zinc-700">
            <div
                class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-accent-content text-accent-foreground">
                <flux:icon name="document-text" class="size-6" />
            </div>
            <div>
                <flux:heading size="xl">{{ $this->sessionCount() }}</flux:heading>
                <flux:text class="mt-1">{{ __('Token Sessions') }}</flux:text>
            </div>
        </div>
    </div>
</div>
