<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    players: Array,
});

const initials = (name) => {
    return name
        .split(' ')
        .map((part) => part.charAt(0))
        .slice(0, 2)
        .join('')
        .toUpperCase();
};

const ranked = computed(() => {
    return [...props.players].sort((a, b) => {
        if (b.win_rate !== a.win_rate) return b.win_rate - a.win_rate;
        return b.matches_played - a.matches_played;
    });
});

const rankBadge = (index) => {
    if (index === 0) return 'bg-amber-100 text-amber-700';
    if (index === 1) return 'bg-slate-200 text-slate-600';
    if (index === 2) return 'bg-orange-100 text-orange-700';
    return 'bg-slate-100 text-slate-400';
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Players
            </h2>
        </template>

        <div class="py-8 sm:py-10">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">

                <div
                    v-if="players.length === 0"
                    class="flex flex-col items-center justify-center rounded-xl bg-white py-16 text-center shadow-[0px_14px_34px_0px_rgba(15,23,42,0.06)] ring-1 ring-slate-900/5"
                >
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
                        <svg class="h-6 w-6 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" />
                        </svg>
                    </div>
                    <p class="mt-4 text-sm font-medium text-slate-900">No players yet</p>
                    <p class="mt-1 text-sm text-slate-500">Players will show up here once they're registered to a team.</p>
                </div>

                <div v-else class="overflow-hidden rounded-xl bg-white shadow-[0px_14px_34px_0px_rgba(15,23,42,0.06)] ring-1 ring-slate-900/5">
                    <Link
                        v-for="(player, index) in ranked"
                        :key="player.id"
                        :href="route('players.show', player.id)"
                        class="group flex items-center gap-4 px-5 py-4 transition hover:bg-slate-50"
                        :class="index !== ranked.length - 1 ? 'border-b border-slate-100' : ''"
                    >
                        <span
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold"
                            :class="rankBadge(index)"
                        >
                            {{ index + 1 }}
                        </span>

                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-500/10 text-xs font-semibold text-blue-600">
                            {{ initials(player.name) }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-slate-900 group-hover:text-blue-600">
                                {{ player.name }}
                            </p>
                            <p class="text-xs text-slate-400">
                                {{ player.matches_played }} {{ player.matches_played === 1 ? 'match' : 'matches' }} played
                            </p>
                        </div>

                        <div class="shrink-0 text-right">
                            <p
                                class="text-sm font-bold"
                                :class="player.win_rate >= 50 ? 'text-emerald-600' : 'text-slate-500'"
                            >
                                {{ player.win_rate }}%
                            </p>
                            <p class="text-xs text-slate-400">win rate</p>
                        </div>

                        <svg class="h-4 w-4 shrink-0 text-slate-300 transition group-hover:text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>