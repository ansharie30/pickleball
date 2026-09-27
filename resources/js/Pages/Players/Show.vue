<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    player: Object,
    stats: Object,
    tournaments: Array,
    standings: Array,
});

const statusConfig = {
    scheduled: { label: 'Scheduled', class: 'bg-gray-100 text-gray-600' },
    in_progress: { label: 'Live', class: 'bg-amber-100 text-amber-700' },
    completed: { label: 'Completed', class: 'bg-green-100 text-green-700' },
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ player.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white rounded-lg shadow-sm p-5">
                        <p class="text-sm text-gray-500">Rating</p>
                        <p class="text-3xl font-bold mt-1">{{ player.rating }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-5">
                        <p class="text-sm text-gray-500">Wins</p>
                        <p class="text-3xl font-bold mt-1 text-green-600">{{ stats.wins }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-5">
                        <p class="text-sm text-gray-500">Losses</p>
                        <p class="text-3xl font-bold mt-1 text-red-500">{{ stats.losses }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-5">
                        <p class="text-sm text-gray-500">Win Rate</p>
                        <p class="text-3xl font-bold mt-1">{{ stats.win_rate }}%</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium mb-4">Teams</h3>
                    <ul class="divide-y divide-gray-200">
                        <li
                            v-for="team in player.teams"
                            :key="team.id"
                            class="py-2 text-sm text-gray-600"
                        >
                            {{ team.name }}
                            <span v-if="team.division?.tournament" class="text-gray-400">
                                — {{ team.division.tournament.name }}
                            </span>
                        </li>
                    </ul>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium mb-4">Tournament Activity</h3>

                    <div v-if="tournaments.length === 0" class="text-sm text-gray-500">
                        This player has not joined any tournaments yet.
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

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium mb-4">Standings</h3>
                    <div v-if="standings.length === 0" class="text-sm text-gray-500">
                        No standings yet.
                    </div>
                    <div v-else class="space-y-6">
                        <div v-for="entry in standings" :key="entry.tournament_name + entry.division_name + entry.my_team">
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

                <Link
                    :href="route('players.index')"
                    class="text-indigo-600 hover:underline text-sm"
                >
                    ← Back to Players
                </Link>

            </div>
        </div>
    </AuthenticatedLayout>
</template>