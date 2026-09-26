<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    division: Object,
    matches: Array,
});

const statusColors = {
    scheduled: 'bg-slate-100 text-slate-500',
    in_progress: 'bg-amber-100 text-amber-700',
    completed: 'bg-emerald-100 text-emerald-700',
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ division.name }} — Matches</h1>
        </template>

        <div class="py-8 sm:py-10">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">

                <div
                    v-if="matches.length === 0"
                    class="flex flex-col items-center justify-center rounded-xl bg-white py-16 text-center shadow-[0px_14px_34px_0px_rgba(15,23,42,0.06)] ring-1 ring-slate-900/5"
                >
                    <p class="text-sm text-slate-500">No matches generated yet for this division.</p>
                </div>

                <div v-else class="overflow-hidden rounded-xl bg-white shadow-[0px_14px_34px_0px_rgba(15,23,42,0.06)] ring-1 ring-slate-900/5">
                    <Link
                        v-for="(match, index) in matches"
                        :key="match.id"
                        :href="route('matches.show', match.id)"
                        class="group flex items-center justify-between gap-4 px-6 py-4 transition hover:bg-slate-50"
                        :class="index !== matches.length - 1 ? 'border-b border-slate-100' : ''"
                    >
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-900 group-hover:text-blue-600">
                                {{ match.team_a?.name ?? 'TBD' }} <span class="text-slate-400 font-normal">vs</span> {{ match.team_b?.name ?? 'TBD' }}
                            </p>
                            <p class="mt-0.5 text-xs text-slate-500">
                                <span v-if="match.round">{{ match.round }}</span>
                                <span v-if="match.court"> · {{ match.court.name }}</span>
                            </p>
                        </div>

                        <span
                            class="shrink-0 inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                            :class="statusColors[match.status]"
                        >
                            {{ match.status.replace('_', ' ') }}
                        </span>
                    </Link>
                </div>

                <Link
                    :href="route('tournaments.show', division.tournament_id)"
                    class="mt-6 inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700 transition"
                >
                    ← Back to Tournament
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>