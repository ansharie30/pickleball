<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import QRCode from 'qrcode';
import LineupManager from './LineupManager.vue';

const props = defineProps({
    match: Object,
    lineupLimit: { type: Number, default: 0 },
    canManageLineups: { type: Boolean, default: false },
});
const isVolleyball = props.match.tournament?.sport?.name === 'Volleyball';

const qrCanvas = ref(null);
const publicUrl = window.location.origin + '/watch/' + props.match.id;

const games = ref(props.match.games ?? []);
const playerStats = ref(props.match.player_stats ?? props.match.playerStats ?? []);

const currentGame = computed(() =>
    games.value.length > 0 ? games.value[games.value.length - 1] : { team_a_score: 0, team_b_score: 0 }
);

watch(
    () => props.match.games,
    (newGames) => {
        if (newGames && newGames.length > 0) games.value = newGames;
    },
    { deep: true }
);

watch(
    () => props.match.player_stats ?? props.match.playerStats,
    (newStats) => {
        if (newStats) playerStats.value = newStats;
    },
    { deep: true }
);

const statFor = (playerId) => {
    return playerStats.value.find(s => String(s.player_profile_id) === String(playerId))?.points ?? 0;
};

const scorePlayer = (playerId, team, action) => {
    router.post(route('matches.player-score', props.match.id), { player_id: playerId, team, action }, {
        preserveScroll: true,
        onError: (e) => console.error(e),
    });
};

const scoreTeam = (team, action) => {
    router.post(route('matches.score', props.match.id), { team, action }, { preserveScroll: true });
};

const startMatch = () => {
    router.patch(route('matches.start', props.match.id), {}, { preserveScroll: true });
};

let channel = null;

onMounted(() => {
    if (window.Echo) {
        channel = window.Echo.channel('match.' + props.match.id);
        channel.listen('.score.updated', (e) => {
            if (e.match) {
                Object.assign(props.match, e.match);
                if (e.match.games) games.value = e.match.games;
                if (e.match.player_stats ?? e.match.playerStats) {
                    playerStats.value = e.match.player_stats ?? e.match.playerStats;
                }
            }
        });
    }

    QRCode.toCanvas(qrCanvas.value, publicUrl, { width: 72 });
});

onUnmounted(() => {
    if (window.Echo) window.Echo.leaveChannel('match.' + props.match.id);
});

const statusConfig = {
    scheduled: { label: 'Scheduled', class: 'bg-slate-100 text-slate-500' },
    in_progress: { label: 'In Progress', class: 'bg-amber-100 text-amber-700' },
    completed: { label: 'Completed', class: 'bg-emerald-100 text-emerald-700' },
};
</script>

<template>
    <div>
        <div class="mb-4 flex items-start justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="shrink-0 rounded-lg bg-white p-1.5 shadow-[0px_14px_34px_0px_rgba(15,23,42,0.06)] ring-1 ring-slate-900/5">
                    <canvas ref="qrCanvas" class="block h-[72px] w-[72px]"></canvas>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium" :class="statusConfig[match.status]?.class">
                            {{ statusConfig[match.status]?.label ?? match.status }}
                        </span>
                        <span v-if="match.status === 'in_progress'" class="text-sm text-slate-500">{{ isVolleyball ? 'Set' : 'Game' }} {{ currentGame.game_number || 1 }}</span>
                    </div>
                    <span v-if="match.status === 'completed'" class="mt-1 block text-sm font-semibold text-emerald-700">
                        Winner: {{ match.winner_team_id === match.team_a_id ? match.team_a.name : match.team_b.name }}
                    </span>
                </div>
            </div>
        </div>

        <div v-if="match.status === 'scheduled'" class="mb-4 text-center">
            <button type="button" @click="startMatch" class="inline-flex items-center rounded-md bg-emerald-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-400 transition">
                Start Match
            </button>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

            <div v-for="team in ['a', 'b']" :key="team" class="overflow-hidden rounded-xl bg-white p-6 shadow-[0px_14px_34px_0px_rgba(15,23,42,0.06)] ring-1 ring-slate-900/5">
                    <h3 class="text-center text-sm font-semibold text-slate-700 mb-2">
                        {{ team === 'a' ? match.team_a.name : match.team_b.name }}
                    </h3>
                    <div class="text-center text-5xl font-bold text-slate-900 mb-4">
                        {{ team === 'a' ? currentGame.team_a_score : currentGame.team_b_score }}
                    </div>

                    <LineupManager
                        v-if="lineupLimit > 0"
                        :match="match"
                        :team-side="team"
                        :limit="lineupLimit"
                        :can-substitute="canManageLineups && match.status !== 'completed'"
                    >
                        <template #starter="{ player }">
                            <div class="flex items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-slate-800"><span class="mr-1 text-slate-400">#{{ player.pivot?.jersey_number ?? '—' }}</span>{{ player.name }}</p>
                                    <p class="text-xs text-slate-400">{{ statFor(player.id) }} pts</p>
                                </div>
                                <div class="flex gap-1 shrink-0">
                                    <button
                                        type="button"
                                        :disabled="match.status !== 'in_progress'"
                                        @click="scorePlayer(player.id, team, 'decrement')"
                                        class="h-7 w-7 rounded bg-slate-200 text-sm text-slate-600 hover:bg-slate-300 disabled:opacity-40"
                                    >−</button>
                                    <button
                                        type="button"
                                        :disabled="match.status !== 'in_progress'"
                                        @click="scorePlayer(player.id, team, 'increment')"
                                        class="h-7 w-7 rounded bg-blue-500 text-sm font-medium text-white hover:bg-blue-400 disabled:opacity-40"
                                    >+1</button>
                                </div>
                            </div>
                        </template>
                            <template #bench="{ player }">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="min-w-0 truncate"><span class="mr-1 text-xs text-slate-400">#{{ player.pivot?.jersey_number ?? '—' }}</span>{{ player.name }}</span>
                                    <span class="shrink-0 text-xs text-slate-400">{{ statFor(player.id) }} pts</span>
                                </div>
                            </template>
                    </LineupManager>

                    <div v-else-if="(team === 'a' ? match.team_a.players : match.team_b.players)?.length" class="space-y-2">
                        <div
                            v-for="player in (team === 'a' ? match.team_a.players : match.team_b.players)"
                            :key="player.id"
                            class="flex items-center justify-between rounded-md bg-slate-50 px-3 py-2"
                        >
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-slate-800"><span class="mr-1 text-slate-400">#{{ player.pivot?.jersey_number ?? '—' }}</span>{{ player.name }}</p>
                                <p class="text-xs text-slate-400">{{ statFor(player.id) }} pts</p>
                            </div>
                            <div class="flex gap-1 shrink-0">
                                <button
                                    type="button"
                                    :disabled="match.status !== 'in_progress'"
                                    @click="scorePlayer(player.id, team, 'decrement')"
                                    class="h-7 w-7 rounded bg-slate-200 text-sm text-slate-600 hover:bg-slate-300 disabled:opacity-40"
                                >−</button>
                                <button
                                    type="button"
                                    :disabled="match.status !== 'in_progress'"
                                    @click="scorePlayer(player.id, team, 'increment')"
                                    class="h-7 w-7 rounded bg-blue-500 text-sm font-medium text-white hover:bg-blue-400 disabled:opacity-40"
                                >+1</button>
                            </div>
                        </div>
                    </div>

                    <div v-else class="flex justify-center gap-2">
                        <button type="button" :disabled="match.status !== 'in_progress'" @click="scoreTeam(team, 'decrement')" class="h-10 w-10 rounded-md bg-slate-100 text-lg text-slate-600 hover:bg-slate-200 disabled:opacity-40">−</button>
                        <button type="button" :disabled="match.status !== 'in_progress'" @click="scoreTeam(team, 'increment')" class="h-10 w-10 rounded-md bg-blue-500 text-lg font-medium text-white hover:bg-blue-400 disabled:opacity-40">+</button>
                    </div>
                </div>

        </div>

        <div v-if="games.length > 1" class="mt-6 flex justify-center gap-3">
            <div
                v-for="game in games"
                :key="game.id ?? game.game_number"
                class="flex flex-col items-center rounded-lg px-4 py-2 ring-1"
                :class="game.game_number === currentGame.game_number ? 'bg-blue-50 ring-blue-200' : 'bg-white ring-slate-200'"
            >
                <span class="text-xs font-medium uppercase tracking-wide text-slate-400 mb-1">{{ isVolleyball ? 'Set' : 'Game' }} {{ game.game_number }}</span>
                <span class="text-sm font-semibold text-slate-700">{{ game.team_a_score }} – {{ game.team_b_score }}</span>
            </div>
        </div>
    </div>
</template>