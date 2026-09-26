<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    stats: Object,
});

const statCards = [
    { label: 'Tournaments', key: 'total_tournaments', icon: 'M8 21h8M12 17v4M7 4h10v4a5 5 0 01-10 0V4zM5 4H3v2a4 4 0 004 4M19 4h2v2a4 4 0 01-4 4' },
    { label: 'Divisions', key: 'total_divisions', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
    { label: 'Teams', key: 'total_teams', icon: 'M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75' },
    { label: 'Courts', key: 'total_courts', icon: 'M4 4h16v16H4zM4 12h16M12 4v16' },
];
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Dashboard</h1>
        </template>

        <div class="py-8 sm:py-10">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 space-y-6">

                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <div
                        v-for="card in statCards"
                        :key="card.key"
                        class="rounded-xl bg-white p-5 shadow-[0px_14px_34px_0px_rgba(15,23,42,0.06)] ring-1 ring-slate-900/5"
                    >
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-500/10">
                                <svg class="h-4.5 w-4.5 text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="card.icon" />
                                </svg>
                            </div>
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ card.label }}</p>
                        </div>
                        <p class="mt-3 text-3xl font-bold text-slate-900">{{ stats[card.key] }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-xl bg-white p-5 shadow-[0px_14px_34px_0px_rgba(15,23,42,0.06)] ring-1 ring-slate-900/5">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-slate-600">Matches In Progress</p>
                            <span class="h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
                        </div>
                        <p class="mt-2 text-3xl font-bold text-amber-600">{{ stats.matches_in_progress }}</p>
                    </div>
                    <div class="rounded-xl bg-white p-5 shadow-[0px_14px_34px_0px_rgba(15,23,42,0.06)] ring-1 ring-slate-900/5">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-slate-600">Matches Completed</p>
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        </div>
                        <p class="mt-2 text-3xl font-bold text-emerald-600">{{ stats.matches_completed }}</p>
                    </div>
                </div>

                <div class="rounded-xl bg-white shadow-[0px_14px_34px_0px_rgba(15,23,42,0.06)] ring-1 ring-slate-900/5 p-6">
                    <h2 class="text-base font-semibold text-slate-900 mb-4">Quick Actions</h2>
                    <div class="flex flex-wrap gap-3">
                        <Link
                            :href="route('tournaments.index')"
                            class="inline-flex items-center rounded-md bg-blue-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-400 transition"
                        >
                            + New Tournament
                        </Link>
                        <Link
                            :href="route('courts.index')"
                            class="inline-flex items-center rounded-md bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-200 transition"
                        >
                            + New Court
                        </Link>
                        <Link
                            :href="route('tournaments.index')"
                            class="inline-flex items-center rounded-md bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-200 transition"
                        >
                            View All Tournaments
                        </Link>
                        <Link
                            :href="route('courts.index')"
                            class="inline-flex items-center rounded-md bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-200 transition"
                        >
                            View All Courts
                        </Link>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>