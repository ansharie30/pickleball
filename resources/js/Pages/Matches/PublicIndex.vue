<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    matches: Array,
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
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

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

            <div v-else class="space-y-3">
                <component
                    :is="match.status !== 'scheduled' ? Link : 'div'"
                    v-for="match in matches"
                    :key="match.id"
                    :href="match.status !== 'scheduled' ? route('matches.public', match.id) : undefined"
                    class="block bg-white rounded-lg ring-1 ring-slate-900/5 p-4 hover:ring-slate-900/10 transition"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-900">
                                {{ match.team_a?.name ?? 'TBD' }}
                                <span class="text-slate-400 font-normal">vs</span>
                                {{ match.team_b?.name ?? 'TBD' }}
                            </p>
                            <p class="text-xs text-slate-400 mt-1">
                                <span v-if="match.tournament">{{ match.tournament.name }}</span>
                                <span v-if="match.tournament && match.round"> · </span>
                                <span v-if="match.round">{{ match.round }}</span>
                                <span v-if="match.court"> · {{ match.court.name }}</span>
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

        </div>
    </div>
</template>