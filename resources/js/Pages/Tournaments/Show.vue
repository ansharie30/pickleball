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
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ tournament.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500">Format</p>
                            <p class="font-medium">{{ tournament.format }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Status</p>
                            <p class="font-medium">{{ tournament.status }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Start Date</p>
                            <p class="font-medium">{{ tournament.start_date }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium mb-4">Divisions</h3>

                    <div class="mb-4">
                        <Link
                            :href="route('divisions.create', tournament.id)"
                            class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700"
                        >
                            + New Division
                        </Link>
                    </div>

                    <div v-if="!tournament.divisions || tournament.divisions.length === 0" class="text-gray-500">
                        No divisions yet.
                    </div>

                    <div v-for="division in tournament.divisions" :key="division.id" class="mb-6">
                        <div class="flex justify-between items-center">
                            <h4 class="font-semibold text-gray-700">{{ division.name }}</h4>
                            <div class="flex gap-2">
                                <Link
                                    :href="route('teams.create', division.id)"
                                    class="text-sm bg-gray-100 hover:bg-gray-200 px-3 py-1 rounded"
                                >
                                    + Register Team
                                </Link>
                                <Link
                                    v-if="tournament.format === 'single_elimination' || tournament.format === 'double_elimination'"
                                    :href="route('divisions.bracket', division.id)"
                                    class="text-sm bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-700"
                                >
                                    View Bracket
                                </Link>
                                <Link
                                    :href="route('divisions.standings', division.id)"
                                    class="text-sm bg-gray-100 hover:bg-gray-200 px-3 py-1 rounded"
                                >
                                    View Standings
                                </Link>
                                <button
                                    @click="generateMatches(division.id)"
                                    :disabled="divisionHasMatches(division)"
                                    class="text-sm bg-gray-100 hover:bg-gray-200 px-3 py-1 rounded disabled:opacity-40 disabled:cursor-not-allowed"
                                >
                                    {{ divisionHasMatches(division) ? 'Matches Generated' : 'Generate Matches' }}
                                </button>
                            </div>
                        </div>

                        <ul class="mt-2 divide-y divide-gray-200">
                            <li
                                v-for="team in division.teams"
                                :key="team.id"
                                class="py-2 text-sm text-gray-600"
                            >
                                {{ team.name }}
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium mb-4">Matches</h3>

                    <div v-if="!tournament.matches || tournament.matches.length === 0" class="text-gray-500">
                        No matches yet.
                    </div>

                    <ul v-else class="divide-y divide-gray-200">
                        <li
                            v-for="match in tournament.matches"
                            :key="match.id"
                            class="py-3 flex justify-between items-center text-sm"
                        >
                            <Link :href="route('matches.show', match.id)" class="text-indigo-600 hover:underline">
                                {{ match.team_a.name }} vs {{ match.team_b.name }}
                            </Link>

                            <div class="flex items-center gap-3">
                                <select
                                    :value="match.court_id"
                                    @change="assignCourt(match.id, $event.target.value)"
                                    class="text-xs border-gray-300 rounded"
                                >
                                    <option value="">No court</option>
                                    <option v-for="court in courts" :key="court.id" :value="court.id">
                                        {{ court.name }}
                                    </option>
                                </select>
                                <span class="text-gray-400">{{ match.status }}</span>
                            </div>
                        </li>
                    </ul>
                </div>

                <Link
                    :href="route('tournaments.index')"
                    class="text-indigo-600 hover:underline text-sm"
                >
                    ← Back to Tournaments
                </Link>

            </div>
        </div>
    </AuthenticatedLayout>
</template>