<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\TokenPreset;

new #[Title('Token Presets')] class extends Component
{
    public function createPreset()
    {
        $preset = TokenPreset::create([
            'name' => 'New Preset',
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('pages.dashboard.token-presets', $preset);
    }

    public function editPreset($presetId, $data = null)
    {
        $preset = TokenPreset::findOrFail($presetId);
        $preset->update($data);
        return redirect()->route('pages.dashboard.token-presets', $presetId);
    }

    public function deletePreset($presetId)
    {
        $preset = TokenPreset::findOrFail($presetId);
        $preset->delete();
        return redirect()->route('pages.dashboard.token-presets');
    }

    public function resetPresets()
    {
        TokenPreset::where('user_id', auth()->id())->delete();
        return redirect()->route('pages.dashboard.token-presets');
    }

    public function getPresets()
    {
        return TokenPreset::all();
    }
};
?>

<!-- Display the token presets available to add to the canvas, enabling the user to edit and delete them.
    The user can also create new token presets.
    The user may also reset to default presets, clearing all customizations with a confirmation modal beforehand.
-->
<div>
    <div class="flex h-full w-full flex-1 flex-col gap-8">
        <div
            class="flex items-center gap-4 rounded-2xl border border-zinc-200 bg-linear-to-br from-(--theme-surface-from) to-(--theme-surface-to) p-6 dark:border-zinc-700">
            <div
                class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-accent-content text-accent-foreground">
                <flux:icon name="document-plus" class="size-6" />
            </div>
            <div>
                <flux:heading size="xl">{{ __('Token Presets') }}</flux:heading>
                <flux:text class="mt-1">
                    {{ __('Create and manage your token presets for use in the play table.') }}
                </flux:text>
            </div>
        </div>
        <div class="rounded-2xl border border-zinc-200 p-6 shadow-sm dark:border-zinc-700">
            <div class="flex flex-col gap-6">
                <div>
                    <flux:heading size="lg">{{ __('Token Presets') }}</flux:heading>
                    <flux:text>{{ __('Manage your token presets for use in the play table.') }}
                    </flux:text>
                </div>

                <!-- Search and filter token presets -->


                <!-- Display the token presets in a grid, with buttons to edit and delete each preset. -->
                <div class="flex flex-col gap-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <flux:button wire:click="createPreset" icon="plus" color="primary" size="sm">
                            {{ __('Create New Preset') }}
                        </flux:button>
                        <flux:button wire:click="resetPresets" icon="trash" color="danger" size="sm">
                            {{ __('Reset to Default Presets') }}
                        </flux:button>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($this->getPresets() as $preset)
                            <div
                                class="flex flex-col gap-2 rounded-xl border border-zinc-200 p-4 shadow-sm dark:border-zinc-700">
                                <div class="flex items-center justify-between gap-2">
                                    <flux:heading size="md">{{ $preset->name }}</flux:heading>
                                    <div class="flex items-center gap-2">
                                        <flux:button wire:click="editPreset({{ $preset->id }})" icon="pencil"
                                            color="primary" size="sm" />
                                        <flux:button wire:click="deletePreset({{ $preset->id }})" icon="trash"
                                            color="danger" size="sm" />
                                    </div>
                                </div>
                                <flux:text class="text-zinc-500 text-sm">
                                    {{ __('Created at') }}:
                                    {{ $preset->created_at->format('M d, Y') }}
                                </flux:text>
                            </div>
                        @endforeach
                    </div>
            </div>
    </div>
</div>
