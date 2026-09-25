<script setup>
import { ref, computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const sidebarOpen = ref(false);
const page = usePage();

const isAdmin = computed(() => page.props.auth.user.role === 'admin');

const navItems = computed(() => {
    if (isAdmin.value) {
        return [
            { name: 'Dashboard', route: 'dashboard', match: 'dashboard' },
            { name: 'Tournaments', route: 'tournaments.index', match: 'tournaments.*' },
            { name: 'Venues', route: 'venues.index', match: 'venues.*' },
            { name: 'Courts', route: 'courts.index', match: 'courts.*' },
            { name: 'Players', route: 'players.index', match: 'players.*' },
        ];
    }

    return [
        { name: 'My Portal', route: 'player.portal', match: 'player.portal' },
    ];
});

const icons = {
    Dashboard: 'M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z',
    Tournaments: 'M8 21h8M12 17v4M7 4h10v4a5 5 0 01-10 0V4zM5 4H3v2a4 4 0 004 4M19 4h2v2a4 4 0 01-4 4',
    Venues: 'M3 21h18M5 21V7l7-4 7 4v14M9 9h1m4 0h1m-6 4h1m4 0h1m-6 4h1m4 0h1',
    Courts: 'M4 4h16v16H4zM4 12h16M12 4v16',
    Players: 'M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75',
    'My Portal': 'M12 4.5a3.5 3.5 0 100 7 3.5 3.5 0 000-7zM4 20a8 8 0 0116 0',
};
</script>

<template>
    <div class="min-h-screen bg-gray-50">

        <!-- Mobile overlay -->
        <div
            v-if="sidebarOpen"
            @click="sidebarOpen = false"
            class="fixed inset-0 bg-black/30 z-40 lg:hidden"
        ></div>

        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 flex flex-col transition-transform duration-200 lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <!-- Logo -->
            <div class="h-16 flex items-center gap-2 px-5 border-b border-gray-200">
                <Link :href="route('dashboard')" class="flex items-center gap-2">
                    <ApplicationLogo class="h-6 w-6 text-gray-900" />
                </Link>
            </div>

            <!-- Nav -->
            <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
                <Link
                    v-for="item in navItems"
                    :key="item.name"
                    :href="route(item.route)"
                    class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors"
                    :class="route().current(item.match)
                        ? 'bg-gray-100 text-gray-900'
                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                >
                    <svg
                        class="h-4 w-4 shrink-0"
                        :class="route().current(item.match) ? 'text-gray-900' : 'text-gray-400'"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path :d="icons[item.name]" />
                    </svg>
                    {{ item.name }}
                </Link>
            </nav>

            <!-- User menu (bottom of sidebar) -->
            <div class="border-t border-gray-200 p-3">
                <Dropdown align="top" width="56">
                    <template #trigger>
                        <button
                            type="button"
                            class="flex items-center gap-3 w-full px-2 py-2 rounded-md text-sm hover:bg-gray-50 transition-colors"
                        >
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-900 text-white text-xs font-medium">
                                {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                            </span>
                            <span class="flex-1 text-left overflow-hidden">
                                <span class="block truncate font-medium text-gray-900">{{ $page.props.auth.user.name }}</span>
                                <span class="block truncate text-xs text-gray-500">{{ $page.props.auth.user.email }}</span>
                            </span>
                            <svg class="h-4 w-4 text-gray-400 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 3a.75.75 0 01.55.24l3.25 3.5a.75.75 0 11-1.1 1.02L10 4.852 7.3 7.76a.75.75 0 01-1.1-1.02l3.25-3.5A.75.75 0 0110 3zm-3.76 9.24a.75.75 0 011.06.02L10 15.148l2.7-2.908a.75.75 0 111.1 1.02l-3.25 3.5a.75.75 0 01-1.1 0l-3.25-3.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </template>

                    <template #content>
                        <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                        <DropdownLink :href="route('logout')" method="post" as="button">
                            Log Out
                        </DropdownLink>
                    </template>
                </Dropdown>
            </div>
        </aside>

        <!-- Main content -->
        <div class="lg:pl-64 flex flex-col min-h-screen">

            <!-- Mobile top bar -->
            <div class="lg:hidden sticky top-0 z-30 flex items-center gap-3 h-14 px-4 bg-white border-b border-gray-200">
                <button
                    @click="sidebarOpen = true"
                    class="p-2 -ml-2 rounded-md text-gray-500 hover:bg-gray-100"
                >
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <ApplicationLogo class="h-5 w-5 text-gray-900" />
            </div>

            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white border-b border-gray-200">
                <div class="px-4 py-5 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1">
                <slot />
            </main>
        </div>
    </div>
</template>