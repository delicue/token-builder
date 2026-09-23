<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Theme settings')] class extends Component {
    public string $theme = 'violet';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->theme = Auth::user()->theme;
    }

    /**
     * The available color themes.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public function themes(): array
    {
        return [
            ['value' => 'violet', 'label' => __('Violet')],
            ['value' => 'emerald', 'label' => __('Emerald')],
            ['value' => 'rose', 'label' => __('Rose')],
            ['value' => 'amber', 'label' => __('Amber')],
            ['value' => 'slate', 'label' => __('Slate')],
        ];
    }

    /**
     * Select and persist a color theme.
     */
    public function selectTheme(string $theme): void
    {
        $this->theme = $theme;

        Auth::user()->update(['theme' => $theme]);

        $this->redirect(route('theme.edit'));
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">{{ __('Theme settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Theme')" :subheading="__('Choose the accent color used across the app')">
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
            @foreach ($this->themes() as $option)
                <button
                    type="button"
                    wire:click="selectTheme('{{ $option['value'] }}')"
                    data-test="theme-option-{{ $option['value'] }}"
                    class="flex flex-col items-center gap-2 rounded-lg border p-3 text-sm transition-colors {{ $theme === $option['value'] ? 'border-accent' : 'border-zinc-200 dark:border-zinc-700' }}"
                >
                    <span class="size-6 rounded-full" data-theme="{{ $option['value'] }}" style="background-color: var(--color-accent-content)"></span>
                    {{ $option['label'] }}
                </button>
            @endforeach
        </div>
    </x-pages::settings.layout>
</section>
