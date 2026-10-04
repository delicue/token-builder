<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" data-theme="{{ auth()->user()?->theme ?? 'violet' }}">
    <head>
        @include('partials.head')
    </head>
    <body class="welcome-page">
        <div class="welcome-atmosphere" aria-hidden="true">
            <span class="welcome-atmosphere__glow"></span>
            <span class="welcome-atmosphere__orbit"></span>
            <span class="welcome-atmosphere__shape welcome-atmosphere__shape--orb"></span>
            <span class="welcome-atmosphere__shape welcome-atmosphere__shape--diamond"></span>
            <span class="welcome-atmosphere__shape welcome-atmosphere__shape--die"></span>
            <span class="welcome-atmosphere__shape welcome-atmosphere__shape--ring"></span>
        </div>

        <header class="welcome-header">
            <a class="welcome-brand" href="{{ route('home') }}" wire:navigate>
                <span class="welcome-brand__mark" aria-hidden="true">T</span>
                <span>{{ config('app.name', 'Token Builder') }}</span>
            </a>
            @if (Route::has('login'))
                <nav class="welcome-nav" aria-label="{{ __('Account') }}">
                    @auth
                        <a class="welcome-nav__link" href="{{ route('pages.dashboard.index') }}" wire:navigate>{{ __('Dashboard') }}</a>
                    @else
                        <a class="welcome-nav__link" href="{{ route('login') }}" wire:navigate>{{ __('Log in') }}</a>

                        @if (Route::has('register'))
                            <a class="welcome-nav__button" href="{{ route('register') }}" wire:navigate>{{ __('Get started') }}</a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>

        <main class="welcome-main">
            <section class="welcome-hero" aria-labelledby="welcome-title">
                <div class="welcome-copy">
                    <p class="welcome-eyebrow"><span></span>{{ __('A little more magic at the table') }}</p>
                    <h1 id="welcome-title">{{ __('Make your table a little more yours.') }}</h1>
                    <p class="welcome-description">
                        {{ __('Create custom tokens with your own labels, roll dice, and keep counters close at hand. Everything you need for a more personal game night.') }}
                    </p>
                    <div class="welcome-actions">
                        @auth
                            <a class="welcome-primary" href="{{ route('pages.dashboard.index') }}" wire:navigate>{{ __('Open your workspace') }}<span class="welcome-primary__arrow" aria-hidden="true"></span></a>
                        @else
                            @if (Route::has('register'))
                                <a class="welcome-primary" href="{{ route('register') }}" wire:navigate>{{ __('Build your first set') }}<flux:icon name="arrow-right" aria-hidden="true" /></a>
                            @endif
                            @if (Route::has('login'))
                                <a class="welcome-secondary" href="{{ route('login') }}" wire:navigate>{{ __('I already have an account') }}</a>
                            @endif
                        @endauth
                    </div>
                </div>

                <div class="welcome-stage" role="img" aria-label="{{ __('A preview of custom Hero and Ally tokens alongside a die') }}">
                    <span class="welcome-stage__halo"></span>
                    <span class="welcome-stage__spark welcome-stage__spark--one"></span>
                    <span class="welcome-stage__spark welcome-stage__spark--two"></span>
                    <div class="welcome-token welcome-token--hero"><span>01</span><strong>{{ __('Hero') }}</strong></div>
                    <div class="welcome-token welcome-token--ally"><span>02</span><strong>{{ __('Ally') }}</strong></div>
                    <div class="welcome-die" aria-hidden="true">
                        <span></span><span></span><span></span><span></span><span></span>
                    </div>
                    <span class="welcome-stage__orbit"></span>
                </div>
            </section>

            <section class="welcome-tools" aria-label="{{ __('Workspace tools') }}">
                <div><span class="welcome-tools__number">01</span><span>{{ __('Custom tokens') }}</span></div>
                <div><span class="welcome-tools__number">02</span><span>{{ __('Quick dice rolls') }}</span></div>
                <div><span class="welcome-tools__number">03</span><span>{{ __('Table counters') }}</span></div>
            </section>
        </main>
    </body>
</html>
