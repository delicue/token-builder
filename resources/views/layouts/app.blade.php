<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main class="flex min-h-[calc(100svh-4rem)] flex-col lg:min-h-svh">
        <div class="w-full flex-1">
            {{ $slot }}
        </div>

        @persist('dashboard-sessions-footer')
            <livewire:dashboard.sessions-footer />
        @endpersist
    </flux:main>
</x-layouts::app.sidebar>
