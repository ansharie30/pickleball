<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import LineupManager from './LineupManager.vue';

const props = defineProps({ match: Object, canManageLineups: Boolean });

const game = computed(() => props.match.games?.[props.match.games.length - 1] ?? { team_a_score: 0, team_b_score: 0 });
const playerStats = ref(props.match.player_stats ?? props.match.playerStats ?? []);
const canUpdateStats = computed(() => props.match.timer_running && props.match.status !== 'completed');

watch(() => props.match.player_stats ?? props.match.playerStats, (v) => { if (v) playerStats.value = v; }, { deep: true });

const statFor = (playerId) => playerStats.value.find(s => String(s.player_profile_id) === String(playerId)) ?? { points: 0, assists: 0, rebounds: 0 };

const periodTotalSeconds = computed(() => Math.round((Number(props.match.tournament?.period_minutes) || 10) * 60));
const remainingFromServer = computed(() => props.match.period_seconds_remaining != null
    ? Math.max(0, Math.floor(Number(props.match.period_seconds_remaining)))
    : periodTotalSeconds.value);

const displaySeconds = ref(remainingFromServer.value);
let tickInterval = null;
let hasTriggeredAutoAdvance = false;

const syncTimer = () => {
    if (props.match.timer_running && props.match.timer_started_at) {
        const startedAtMs = new Date(props.match.timer_started_at).getTime();
        const elapsed = Math.max(0, Math.floor((Date.now() - startedAtMs) / 1000));
        displaySeconds.value = Math.max(0, remainingFromServer.value - elapsed);
    } else {
        displaySeconds.value = remainingFromServer.value;
    }
};

const nextPeriod = () => router.patch(route('matches.timer.next-period', props.match.id), {}, { preserveScroll: true });

watch(displaySeconds, (val) => {
    if (val <= 0 && props.match.timer_running && !hasTriggeredAutoAdvance && props.match.status !== 'completed') {
        hasTriggeredAutoAdvance = true;
        nextPeriod();
    }
});

watch(() => props.match.current_period, () => { hasTriggeredAutoAdvance = false; });

onMounted(() => {
    syncTimer();
    tickInterval = setInterval(syncTimer, 1000);

    if (window.Echo) {
        window.Echo.channel('match.' + props.match.id).listen('.score.updated', (e) => {
            Object.assign(props.match, e.match);
            if (e.match.player_stats ?? e.match.playerStats) {
                playerStats.value = e.match.player_stats ?? e.match.playerStats;
            }
            syncTimer();
        });
    }
});

onUnmounted(() => clearInterval(tickInterval));

const formattedTime = computed(() => {
    const total = Math.max(0, Math.floor(displaySeconds.value));
    const m = Math.floor(total / 60);
    const s = total % 60;
    return `${m}:${s.toString().padStart(2, '0')}`;
});

const scorePlayer = (playerId, team, points) => {
    router.post(route('matches.basketball-player-score', props.match.id), { player_id: playerId, team, points }, { preserveScroll: true });
};

const statAction = (playerId, team, stat, action) => {
    router.post(route('matches.basketball-stat', props.match.id), { player_id: playerId, team, stat, action }, { preserveScroll: true });
};

const startTimer = () => router.patch(route('matches.timer.start', props.match.id), {}, { preserveScroll: true });
const pauseTimer = () => router.patch(route('matches.timer.pause', props.match.id), {}, { preserveScroll: true });
</script>

<template>
    <div class="text-center mb-6">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                Quarter {{ match.current_period || 1 }} of {{ match.tournament.periods ?? 4 }}
            </p>
            <p class="text-4xl font-bold text-slate-900 mt-1">{{ formattedTime }}</p>
            <div class="mt-3 flex justify-center gap-2">
                <button v-if="!match.timer_running" @click="startTimer" :disabled="match.status === 'completed'" class="rounded-md bg-emerald-500 px-4 py-1.5 text-xs font-semibold text-white hover:bg-emerald-400 disabled:opacity-40">Start</button>
                <button v-else @click="pauseTimer" class="rounded-md bg-amber-500 px-4 py-1.5 text-xs font-semibold text-white hover:bg-amber-400">Pause</button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div v-for="team in ['a', 'b']" :key="team" class="overflow-hidden rounded-xl bg-white p-6 shadow-[0px_14px_34px_0px_rgba(15,23,42,0.06)] ring-1 ring-slate-900/5">
                <h3 class="text-center text-sm font-semibold text-slate-700 mb-1">{{ team === 'a' ? match.team_a.name : match.team_b.name }}</h3>
                <div class="text-center text-4xl font-bold text-slate-900 mb-4">{{ team === 'a' ? game.team_a_score : game.team_b_score }}</div>

                <LineupManager
                    :match="match"
                    :team-side="team"
                    :limit="5"
                    :can-substitute="canManageLineups && match.status !== 'completed'"
                >
                    <template #starter="{ player }">
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <p class="truncate text-sm font-medium text-slate-800"><span class="mr-1 text-slate-400">#{{ player.pivot?.jersey_number ?? '—' }}</span>{{ player.name }}</p>
                            <p class="shrink-0 text-xs text-slate-400">
                                {{ statFor(player.id).points }}p · {{ statFor(player.id).assists }}a · {{ statFor(player.id).rebounds }}r
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="text-[10px] font-medium uppercase text-slate-400 w-4">Pts</span>
                            <button v-for="pts in [1, 2, 3]" :key="pts" :disabled="!canUpdateStats" @click="scorePlayer(player.id, team, pts)" class="h-7 w-7 rounded bg-blue-500 text-xs font-semibold text-white hover:bg-blue-400 disabled:opacity-40">+{{ pts }}</button>
                            <button :disabled="!canUpdateStats" @click="scorePlayer(player.id, team, -1)" class="h-7 w-7 rounded bg-slate-200 text-xs text-slate-600 hover:bg-slate-300 disabled:opacity-40">−1</button>
                        </div>

                        <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                            <span class="text-[10px] font-medium uppercase text-slate-400 w-4">Ast</span>
                            <button :disabled="!canUpdateStats" @click="statAction(player.id, team, 'assists', 'decrement')" class="h-6 w-6 rounded bg-slate-200 text-xs text-slate-600 hover:bg-slate-300 disabled:opacity-40">−</button>
                            <button :disabled="!canUpdateStats" @click="statAction(player.id, team, 'assists', 'increment')" class="h-6 w-6 rounded bg-emerald-500 text-xs font-semibold text-white hover:bg-emerald-400 disabled:opacity-40">+</button>

                            <span class="ml-2 text-[10px] font-medium uppercase text-slate-400 w-4">Reb</span>
                            <button :disabled="!canUpdateStats" @click="statAction(player.id, team, 'rebounds', 'decrement')" class="h-6 w-6 rounded bg-slate-200 text-xs text-slate-600 hover:bg-slate-300 disabled:opacity-40">−</button>
                            <button :disabled="!canUpdateStats" @click="statAction(player.id, team, 'rebounds', 'increment')" class="h-6 w-6 rounded bg-amber-500 text-xs font-semibold text-white hover:bg-amber-400 disabled:opacity-40">+</button>
                        </div>
                    </template>
                    <template #bench="{ player }">
                        <div class="flex items-center justify-between gap-2">
                            <span class="min-w-0 truncate"><span class="mr-1 text-xs text-slate-400">#{{ player.pivot?.jersey_number ?? '—' }}</span>{{ player.name }}</span>
                            <span class="shrink-0 text-xs text-slate-400">
                                {{ statFor(player.id).points }}p · {{ statFor(player.id).assists }}a · {{ statFor(player.id).rebounds }}r
                            </span>
                        </div>
                    </template>
                </LineupManager>
            </div>
        </div>

        <p v-if="match.status === 'completed'" class="mt-6 text-center text-sm font-semibold text-emerald-700">
            Final — Winner: {{ match.winner_team_id === match.team_a_id ? match.team_a.name : match.team_b.name }}
        </p>
</template>