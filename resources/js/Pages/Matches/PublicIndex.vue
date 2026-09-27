<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    matches: Array,
});

const tournamentGroups = computed(() => {
    const groups = new Map();

    props.matches.forEach((match) => {
        const tournamentId = match.tournament?.id ?? 'unassigned';

        if (!groups.has(tournamentId)) {
            groups.set(tournamentId, {
                id: tournamentId,
                name: match.tournament?.name ?? 'No Tournament',
                matches: [],
            });
        }

        groups.get(tournamentId).matches.push(match);
    });

    return [...groups.values()].sort((first, second) => first.name.localeCompare(second.name));
});

const statusConfig = {
    scheduled: { label: 'Scheduled', dot: 'bg-gray-400', text: 'text-gray-500' },
    in_progress: { label: 'Live', dot: 'bg-red-500 animate-pulse', text: 'text-red-600' },
    completed: { label: 'Completed', dot: 'bg-green-500', text: 'text-green-600' },
};
</script>

<template>
    <Head title="Live Scores" />

    <div class="min-h-screen bg-gray-50">
        <div class="w-full px-4 sm:px-6 lg:px-8 xl:px-10 py-10">

            <div class="mb-8">
                <Link :href="route('welcome')" class="text-sm text-slate-500 hover:text-slate-700">
                    ← Back to Home
                </Link>
                <h1 class="mt-3 text-2xl font-bold text-slate-900">Live Scores</h1>
                <p class="mt-1 text-sm text-slate-500">All matches across tournaments and courts, updated in real time.</p>
            </div>

            <div v-if="matches.length === 0" class="text-center py-16 text-slate-400">
                No matches yet.
            </div>

            <div v-else class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
                <section
                    v-for="group in tournamentGroups"
                    :key="group.id"
                    class="min-w-0 rounded-lg bg-white p-5 ring-1 ring-slate-900/10"
                >
                    <div class="mb-4 flex items-baseline justify-between gap-3 border-b border-slate-200 pb-3">
                        <h2 class="min-w-0 text-base font-semibold text-slate-900">{{ group.name }}</h2>
                        <span class="text-xs text-slate-400">{{ group.matches.length }} matches</span>
                    </div>

                    <div class="space-y-3">
                        <component
                            :is="match.status !== 'scheduled' ? Link : 'div'"
                            v-for="match in group.matches"
                            :key="match.id"
                            :href="match.status !== 'scheduled' ? route('matches.public', match.id) : undefined"
                            class="block rounded-md bg-slate-50 p-3 ring-1 ring-slate-900/5 transition hover:ring-slate-900/15"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-900">
                                        {{ match.team_a?.name ?? 'TBD' }}
                                        <span class="text-slate-400 font-normal">vs</span>
                                        {{ match.team_b?.name ?? 'TBD' }}
                                    </p>
                                    <p class="text-xs text-slate-400 mt-1">
                                        <span v-if="match.round">{{ match.round }}</span>
                                        <span v-if="match.court"> · {{ match.court.name }}</span>
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ match.scheduled_at ? new Date(match.scheduled_at).toLocaleString() : 'No date indicated' }}
                                    </p>
                                </div>

                                <div class="flex items-center gap-2 shrink-0 ml-4">
                                    <span class="h-2 w-2 rounded-full" :class="statusConfig[match.status]?.dot"></span>
                                    <span class="text-xs font-medium" :class="statusConfig[match.status]?.text">
                                        {{ statusConfig[match.status]?.label ?? match.status }}
                                    </span>
                                </div>
                            </div>
                        </component>
                    </div>
                </section>
            </div>

        </div>
    </div>
</template>