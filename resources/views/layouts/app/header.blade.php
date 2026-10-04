<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden mr-2" icon="bars-2" inset="left" />

            <x-app-logo href="{{ route('events.index') }}" wire:navigate />

            <flux:navbar class="-mb-px max-lg:hidden">
                <flux:navbar.item icon="layout-grid" :href="route('events.index')" :current="request()->routeIs('events.index')" wire:navigate>
                    イベント一覧
                </flux:navbar.item>
                @auth
                    <flux:navbar.item icon="plus" :href="route('events.create')" :current="request()->routeIs('events.create')" wire:navigate>
                        イベントを登録
                    </flux:navbar.item>
                @endauth
            </flux:navbar>

            <flux:spacer />


            @auth
                <x-desktop-user-menu />
            @else
                <div class="flex items-center gap-2">
                    <flux:button :href="route('login')" variant="ghost" size="sm" wire:navigate>ログイン</flux:button>
                    <flux:button :href="route('register')" variant="primary" size="sm" wire:navigate>会員登録</flux:button>
                </div>
            @endauth
        </flux:header>

        <!-- Mobile Menu -->
        <flux:sidebar collapsible="mobile" sticky class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('events.index') }}" wire:navigate />
                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>

                <flux:sidebar.item icon="layout-grid" :href="route('events.index')" :current="request()->routeIs('events.index')" wire:navigate>
                    イベント一覧
                </flux:sidebar.item>
                @auth
                    <flux:sidebar.item icon="plus" :href="route('events.create')" :current="request()->routeIs('events.create')" wire:navigate>
                        イベントを登録
                    </flux:sidebar.item>
                @else
                    <flux:sidebar.item icon="arrow-right-end-on-rectangle" :href="route('login')" wire:navigate>ログイン</flux:sidebar.item>
                    <flux:sidebar.item icon="user-plus" :href="route('register')" wire:navigate>会員登録</flux:sidebar.item>
                @endauth
            </flux:sidebar.nav>

            <flux:spacer />

        </flux:sidebar>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
