<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    player: Object,
    teams: Array,
    matches: Array,
    tournaments: Array,
    standings: Array,
    stats: Object,
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

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white rounded-lg shadow-sm p-5">
                        <p class="text-sm text-gray-500">Rating</p>
                        <p class="text-3xl font-bold mt-1">{{ player?.rating ?? 0 }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-5">
                        <p class="text-sm text-gray-500">Wins</p>
                        <p class="text-3xl font-bold mt-1 text-green-600">{{ stats?.wins ?? 0 }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-5">
                        <p class="text-sm text-gray-500">Losses</p>
                        <p class="text-3xl font-bold mt-1 text-red-500">{{ stats?.losses ?? 0 }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-5">
                        <p class="text-sm text-gray-500">Win Rate</p>
                        <p class="text-3xl font-bold mt-1">{{ stats?.win_rate ?? 0 }}%</p>
                    </div>
                </div>

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

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium mb-4">Tournament Activity</h3>

                    <div v-if="tournaments.length === 0" class="text-sm text-gray-500">
                        You have not joined any tournaments yet.
                    </div>

                    <div v-else class="space-y-6">
                        <div
                            v-for="tournament in tournaments"
                            :key="tournament.id"
                            class="rounded-lg border border-gray-200 p-4"
                        >
                            <div class="flex items-center justify-between gap-3 mb-4">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ tournament.name }}</p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ tournament.format }} · {{ tournament.start_date || 'No date' }}
                                    </p>
                                </div>
                                <span class="text-xs px-2 py-1 rounded-full bg-slate-100 text-slate-600">
                                    {{ tournament.status }}
                                </span>
                            </div>

                            <div class="grid gap-4 md:grid-cols-3">
                                <div v-for="status in ['completed', 'in_progress', 'scheduled']" :key="status" class="space-y-2">
                                    <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        {{ statusConfig[status]?.label ?? status }}
                                    </h4>

                                    <div v-if="tournament.matches[status]?.length">
                                        <div
                                            v-for="match in tournament.matches[status]"
                                            :key="match.id"
                                            class="rounded border border-gray-200 bg-gray-50 p-2 text-xs text-gray-600"
                                        >
                                            <p class="font-medium text-gray-800">
                                                {{ match.team_a?.name ?? 'TBD' }} vs {{ match.team_b?.name ?? 'TBD' }}
                                            </p>
                                            <p v-if="match.round" class="mt-1 text-gray-500">{{ match.round }}</p>
                                            <p class="mt-1 text-gray-500">
                                                {{ match.scheduled_at ? new Date(match.scheduled_at).toLocaleString() : 'No date indicated' }}
                                            </p>
                                            <span
                                                v-if="match.status"
                                                class="mt-2 inline-flex rounded px-2 py-0.5 text-[10px] font-medium"
                                                :class="statusConfig[match.status]?.class"
                                            >
                                                {{ statusConfig[match.status]?.label ?? match.status }}
                                            </span>
                                            <span
                                                v-if="match.result"
                                                class="mt-2 ml-1 inline-flex rounded px-2 py-0.5 text-[10px] font-medium"
                                                :class="match.result === 'win' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                                            >
                                                {{ match.result === 'win' ? 'Win' : 'Loss' }}
                                            </span>
                                        </div>
                                    </div>

                                    <p v-else class="text-xs text-gray-400">No {{ statusConfig[status]?.label ?? status.toLowerCase() }} matches.</p>
                                </div>
                            </div>
                        </div>
                    </div>
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