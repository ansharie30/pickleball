<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    tournament: Object,
    courts: Array,
});

const assignCourt = (matchId, courtId) => {
    router.patch(route('matches.assign-court', matchId), { court_id: courtId }, { preserveScroll: true });
};

const generateMatches = (divisionId) => {
    router.post(route('divisions.generate-matches', divisionId));
};

const divisionHasMatches = (division) => {
    const teamIds = division.teams.map(t => t.id);
    return props.tournament.matches.some(m => teamIds.includes(m.team_a_id) || teamIds.includes(m.team_b_id));
};

const statusColors = {
    scheduled: 'bg-gray-100 text-gray-500',
    in_progress: 'bg-amber-100 text-amber-700',
    completed: 'bg-emerald-100 text-emerald-700',
};

const formatLabels = {
    single_elimination: 'Single Elimination',
    double_elimination: 'Double Elimination',
    round_robin: 'Round Robin',
    pool_play: 'Pool Play',
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ tournament.name }}</h1>
        </template>

        <div class="py-8 sm:py-10">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Overview -->
                <div class="rounded-xl bg-white shadow-[0px_14px_34px_0px_rgba(15,23,42,0.06)] ring-1 ring-slate-900/5 p-6">
                    <div class="grid grid-cols-3 gap-4 text-center sm:text-left">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Format</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ formatLabels[tournament.format] ?? tournament.format }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Status</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900 capitalize">{{ tournament.status }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Start Date</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ tournament.start_date }}</p>
                        </div>
                    </div>
                </div>

                <!-- Divisions -->
                <div class="rounded-xl bg-white shadow-[0px_14px_34px_0px_rgba(15,23,42,0.06)] ring-1 ring-slate-900/5">
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                        <h2 class="text-base font-semibold text-slate-900">Divisions</h2>
                        <Link
                            :href="route('divisions.create', tournament.id)"
                            class="inline-flex items-center rounded-md bg-blue-500 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-400 transition"
                        >
                            + New Division
                        </Link>
                    </div>

                    <div v-if="!tournament.divisions || tournament.divisions.length === 0" class="px-6 py-10 text-center text-sm text-slate-400">
                        No divisions yet.
                    </div>

                    <div v-else class="divide-y divide-slate-100">
                        <div v-for="division in tournament.divisions" :key="division.id" class="px-6 py-5">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <h3 class="text-sm font-semibold text-slate-800">{{ division.name }}</h3>

                                <div class="flex flex-wrap gap-2">
                                    <Link
                                        :href="route('teams.create', division.id)"
                                        class="inline-flex items-center rounded-md bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-200 transition"
                                    >
                                        + Register Team
                                    </Link>
                                    <Link
                                        v-if="tournament.format === 'single_elimination' || tournament.format === 'double_elimination'"
                                        :href="route('divisions.bracket', division.id)"
                                        class="inline-flex items-center rounded-md bg-blue-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-400 transition"
                                    >
                                        View Bracket
                                    </Link>
                                    <Link
                                        :href="route('divisions.standings', division.id)"
                                        class="inline-flex items-center rounded-md bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-200 transition"
                                    >
                                        Standings
                                    </Link>
                                    <Link
                                        :href="route('divisions.matches', division.id)"
                                        class="inline-flex items-center rounded-md bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-200 transition"
                                    >
                                        Matches
                                    </Link>
                                    <button
                                        @click="generateMatches(division.id)"
                                        :disabled="divisionHasMatches(division)"
                                        class="inline-flex items-center rounded-md bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-200 transition disabled:cursor-not-allowed disabled:opacity-40"
                                    >
                                        {{ divisionHasMatches(division) ? 'Matches Generated' : 'Generate Matches' }}
                                    </button>
                                </div>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <span
                                    v-for="team in division.teams"
                                    :key="team.id"
                                    class="inline-flex items-center rounded-full bg-slate-50 px-3 py-1 text-xs text-slate-600 ring-1 ring-slate-200"
                                >
                                    {{ team.name }}
                                </span>
                                <span v-if="division.teams.length === 0" class="text-xs text-slate-400">No teams registered yet.</span>
                            </div>
                        </div>
                    </div>
                </div>


                <Link
                    :href="route('tournaments.index')"
                    class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700 transition"
                >
                    ← Back to Tournaments
                </Link>

            </div>
        </div>
    </AuthenticatedLayout>
</template>