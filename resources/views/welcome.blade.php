<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="flex min-h-screen flex-col items-center bg-white text-zinc-900 dark:bg-zinc-800 dark:text-white">
        <header class="flex w-full max-w-4xl items-center justify-between p-6 lg:p-8">

            @if (Route::has('login'))
                <nav class="flex items-center gap-4 text-sm">
                    @auth
                        <flux:button :href="route('pages.dashboard.index')" wire:navigate>{{ __('Dashboard') }}</flux:button>
                    @else
                        <flux:button variant="ghost" :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:button>

                        @if (Route::has('register'))
                            <flux:button :href="route('register')" wire:navigate>{{ __('Register') }}</flux:button>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>

        <main class="flex w-full max-w-4xl flex-1 flex-col items-center justify-center gap-10 px-6 pb-16 text-center">

            <div class="flex flex-col items-center gap-3">
                <flux:heading size="xl">{{ config('app.name', 'Token Builder') }}</flux:heading>
                <flux:text class="max-w-md">
                    {{ __('Create custom tokens with your own labels, roll dice, and create counters — all in one clean workspace.') }}
                </flux:text>
            </div>

            <div class="flex items-center gap-8">
                <div class="flex size-20 items-center justify-center rounded-full border-2 border-zinc-300 bg-zinc-50 text-sm font-semibold dark:border-zinc-600 dark:bg-zinc-900">
                    {{ __('Hero') }}
                </div>
                <div class="flex size-20 items-center justify-center rounded-md border-2 border-zinc-300 bg-zinc-50 text-sm font-semibold dark:border-zinc-600 dark:bg-zinc-900">
                    {{ __('Ally') }}
                </div>
                <div class="grid size-16 grid-cols-3 grid-rows-3 place-items-center rounded-lg border-2 border-zinc-300 bg-zinc-50 p-2 dark:border-zinc-600 dark:bg-zinc-900">
                    <span class="col-start-1 row-start-1 size-2 rounded-full bg-current"></span>
                    <span class="col-start-3 row-start-1 size-2 rounded-full bg-current"></span>
                    <span class="col-start-2 row-start-2 size-2 rounded-full bg-current"></span>
                    <span class="col-start-1 row-start-3 size-2 rounded-full bg-current"></span>
                    <span class="col-start-3 row-start-3 size-2 rounded-full bg-current"></span>
                </div>
            </div>

            @if (! auth()->check() && Route::has('register'))
                <flux:button :href="route('register')" wire:navigate>{{ __('Get started') }}</flux:button>
            @endif
        </main>
    </body>
</html>
