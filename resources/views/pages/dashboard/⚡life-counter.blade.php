<?php

use Livewire\Component;

new class extends Component {
    //
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
            <flux:heading size="xl">{{ __('Life Counter') }}</flux:heading>
            <flux:text class="mt-1">
                {{ __('Track life totals for your game.') }}
            </flux:text>
        </div>
    </div>

    <div class="rounded-2xl border border-zinc-200 p-6 shadow-sm dark:border-zinc-700">
        <div class="flex flex-col gap-6">
            <div>
                <flux:heading size="lg">{{ __('Life Counter') }}</flux:heading>
                <flux:text>{{ __('Keep track of each player\'s life total throughout your game.') }}
                </flux:text>
            </div>
        </div>
    </div>
</div>
