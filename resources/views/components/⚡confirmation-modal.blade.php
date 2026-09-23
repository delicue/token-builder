<?php

use Livewire\Component;

new class extends Component {
    public string $title = 'Are you sure?';
    public string $description = 'This action cannot be undone.';
    public string $confirmButtonText = 'Confirm';
    public string $cancelButtonText = 'Cancel';
};
?>

<div>
    <flux:modal.trigger name="delete-profile">
        <flux:button variant="danger">Delete</flux:button>
    </flux:modal.trigger>

    <flux:modal name="delete-profile" class="min-w-88">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete project?</flux:heading>

                <flux:text class="mt-2">
                    You're about to delete this project.<br>
                    This action cannot be reversed.
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="danger">Delete project</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
