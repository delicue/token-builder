<?php

use Livewire\Component;

new class extends Component {
    public int $counterLimit = 64;
    public array $counters = [];

    public function addCounter()
    {
        if (count($this->counters) < $this->counterLimit) {
            $this->counters[] = 1;
        }
    }

    public function increment($index)
    {
        $this->counters[$index]++;
    }

    public function decrement($index)
    {
        $this->counters[$index]--;
    }
};
?>
<!-- Open grid where every spot holds a button with a plus icon. When the button is clicked, it creates a rounded square with a counter inside. Counters can be incremented or decremented as needed. -->
<div class="flex h-full w-full flex-1 flex-col gap-8">
    <flux:button
        icon="plus"
        wire:click="addCounter"
        class="max-w-1/2 mx-auto">
        {{ __('Add Counter') }}
    </flux:button>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-6">
        @for ($i = 0; $i < count($counters); $i++)
        <!-- Counter block -->
        <div class="flex flex-col justify-center bg-zinc-100 dark:bg-zinc-700 rounded-2xl">
            <div
                class="flex flex-nowrap items-center justify-between rounded-2xl gap-2">
                    <flux:button wire:click="decrement({{ $i }})">-</flux:button>
                    <span class="mx-2 sm:mx-6" wire:click=''>{{ $counters[$i] }}</span>
                    <flux:button wire:click="increment({{ $i }})">+</flux:button>
            </div>
            <div class="flex justify-center mt-2">
                <!-- Reset button -->
                <flux:button size="sm" variant="ghost" class="w-auto mx-auto" wire:click="$set('counters.{{ $i }}', 0)">{{ __('Reset') }}</flux:button>
                <!-- Delete button -->
                <flux:button size="sm" variant="danger" class="w-auto mx-auto" wire:click="$unset('counters.{{ $i }}')">{{ __('Delete') }}</flux:button>
            </div>
        </div>
        <!-- End of counter block -->
        @endfor
    </div>
    <!-- End of counters grid -->
    <!-- Clear counters button -->
    <flux:button
        icon="trash"
        wire:click="$set('counters', [])"
        class="max-w-1/2 mx-auto">
        {{ __('Clear Counters') }}
    </flux:button>
</div>
