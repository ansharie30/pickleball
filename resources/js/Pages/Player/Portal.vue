<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    teams: Array,
    matches: Array,
    standings: Array,
});

const statusConfig = {
    scheduled: { label: 'Scheduled', class: 'bg-gray-100 text-gray-600' },
    in_progress: { label: 'Live', class: 'bg-red-100 text-red-600' },
    completed: { label: 'Completed', class: 'bg-green-100 text-green-600' },
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                My Portal
            </h2>
        </template>

        <div class="py-10">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">

                <!-- My Teams -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium mb-4">My Teams</h3>
                    <div v-if="teams.length === 0" class="text-gray-500 text-sm">
                        You're not registered on any team yet.
                    </div>
                    <ul v-else class="divide-y divide-gray-200">
                        <li v-for="team in teams" :key="team.id" class="py-3 text-sm">
                            <p class="font-medium text-gray-900">{{ team.name }}</p>
                            <p class="text-gray-500 text-xs">
                                {{ team.division?.name }} — {{ team.division?.tournament?.name }}
                            </p>
                        </li>
                    </ul>
                </div>

                <!-- My Matches -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium mb-4">My Matches</h3>
                    <div v-if="matches.length === 0" class="text-gray-500 text-sm">
                        No matches scheduled yet.
                    </div>
                    <ul v-else class="divide-y divide-gray-200">
                        <li
                            v-for="match in matches"
                            :key="match.id"
                            class="py-3 flex justify-between items-center text-sm"
                        >
                            <Link :href="route('matches.public', match.id)" class="hover:underline">
                                <p class="font-medium text-gray-900">
                                    {{ match.team_a?.name }} vs {{ match.team_b?.name }}
                                </p>
                                <p class="text-xs text-gray-400">
                                    {{ match.tournament?.name }}
                                    <span v-if="match.round"> · {{ match.round }}</span>
                                    <span v-if="match.court"> · {{ match.court.name }}</span>
                                </p>
                            </Link>
                            <span
                                class="text-xs px-2 py-1 rounded"
                                :class="statusConfig[match.status]?.class"
                            >
                                {{ statusConfig[match.status]?.label ?? match.status }}
                            </span>
                        </li>
                    </ul>
                </div>

                <!-- My Standings -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium mb-4">My Standings</h3>
                    <div v-if="standings.length === 0" class="text-gray-500 text-sm">
                        No standings yet.
                    </div>
                    <div v-else class="space-y-6">
                        <div v-for="entry in standings" :key="entry.division_name + entry.tournament_name">
                            <p class="text-sm font-semibold text-gray-800">
                                {{ entry.tournament_name }} — {{ entry.division_name }}
                            </p>
                            <p class="text-xs text-gray-500 mb-2">
                                Your team ({{ entry.my_team }}) is ranked #{{ entry.rank }} of {{ entry.total_teams }}
                            </p>
                            <ul class="divide-y divide-gray-100 text-sm">
                                <li
                                    v-for="(row, idx) in entry.standings"
                                    :key="row.team_id"
                                    class="py-2 flex justify-between"
                                    :class="row.team_name === entry.my_team ? 'font-semibold text-indigo-600' : 'text-gray-600'"
                                >
                                    <span>{{ idx + 1 }}. {{ row.team_name }}</span>
                                    <span>{{ row.wins }}W - {{ row.losses }}L</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>