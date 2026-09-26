<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    match: Object,
});

const games = ref(props.match.games ?? []);
const playerStats = ref(props.match.player_stats ?? props.match.playerStats ?? []);
const isBasketball = props.match.tournament?.sport?.scoring_type === 'timed_period';
const currentPeriod = ref(props.match.current_period ?? 1);
const timerRunning = ref(Boolean(props.match.timer_running));
const timerStartedAt = ref(props.match.timer_started_at);
const periodTotalSeconds = Math.round((Number(props.match.tournament?.period_minutes) || 10) * 60);
const periodSecondsRemaining = ref(props.match.period_seconds_remaining ?? periodTotalSeconds);
const displaySeconds = ref(periodSecondsRemaining.value);

const currentGame = ref(
    games.value.length > 0
        ? games.value[games.value.length - 1]
        : { team_a_score: 0, team_b_score: 0, game_number: 1 }
);

const status = ref(props.match.status);
const winnerTeamId = ref(props.match.winner_team_id);

const statFor = (playerId) => playerStats.value.find(
    stat => String(stat.player_profile_id) === String(playerId)
) ?? { points: 0, assists: 0, rebounds: 0 };

const syncTimer = () => {
    if (timerRunning.value && timerStartedAt.value) {
        const startedAtMs = new Date(timerStartedAt.value).getTime();
        const elapsed = Math.max(0, Math.floor((Date.now() - startedAtMs) / 1000));
        displaySeconds.value = Math.max(0, Number(periodSecondsRemaining.value) - elapsed);
    } else {
        displaySeconds.value = Math.max(0, Number(periodSecondsRemaining.value));
    }
};

const formattedTime = computed(() => {
    const total = Math.max(0, Math.floor(displaySeconds.value));
    return `${Math.floor(total / 60)}:${String(total % 60).padStart(2, '0')}`;
});

const timerState = computed(() => {
    if (status.value === 'completed') return 'Final';
    if (timerRunning.value) return 'Running';
    return status.value === 'scheduled' ? 'Not started' : 'Paused';
});

let channel = null;
let timerInterval = null;

onMounted(() => {
    syncTimer();
    timerInterval = setInterval(syncTimer, 1000);

    if (window.Echo) {
        channel = window.Echo.channel('match.' + props.match.id);
        channel.listen('.score.updated', (e) => {
            if (e.match) {
                if (e.match.games && e.match.games.length > 0) {
                    games.value = e.match.games;
                    currentGame.value = e.match.games[e.match.games.length - 1];
                }
                if (e.match.player_stats ?? e.match.playerStats) {
                    playerStats.value = e.match.player_stats ?? e.match.playerStats;
                }
                status.value = e.match.status;
                winnerTeamId.value = e.match.winner_team_id;
                currentPeriod.value = e.match.current_period ?? currentPeriod.value;
                timerRunning.value = Boolean(e.match.timer_running);
                timerStartedAt.value = e.match.timer_started_at;
                periodSecondsRemaining.value = e.match.period_seconds_remaining ?? periodTotalSeconds;
                syncTimer();
            }
        });
    }
});

onUnmounted(() => {
    clearInterval(timerInterval);
    if (window.Echo) {
        window.Echo.leaveChannel('match.' + props.match.id);
    }
});
</script>

<template>
    <div class="min-h-screen bg-gray-900 text-white flex flex-col items-center justify-center p-6">

        <div v-if="status === 'completed'" class="mb-8 text-center">
            <p class="text-2xl font-bold text-green-400">
                Match Complete
            </p>
            <p class="text-lg text-gray-300 mt-1">
                Winner: {{ winnerTeamId === match.team_a_id ? match.team_a.name : match.team_b.name }}
            </p>
        </div>
        <div v-else-if="!isBasketball" class="mb-8 text-gray-400 text-lg">
            Game {{ currentGame.game_number || 1 }}
        </div>

        <div v-if="isBasketball" class="mb-8 text-center">
            <p class="text-sm font-medium uppercase tracking-wide text-gray-400">
                Quarter {{ currentPeriod }} of {{ match.tournament.periods ?? 4 }}
            </p>
            <p class="mt-1 text-5xl font-bold tabular-nums md:text-6xl">{{ formattedTime }}</p>
            <p class="mt-2 text-sm text-gray-400">{{ timerState }}</p>
        </div>

        <div class="grid w-full max-w-6xl grid-cols-1 gap-6 text-center sm:grid-cols-2">
            <section v-for="team in ['a', 'b']" :key="team" class="rounded-xl bg-white/5 p-5 ring-1 ring-white/10 md:p-7">
                <h2 class="mb-4 text-xl font-medium md:text-2xl">{{ team === 'a' ? match.team_a.name : match.team_b.name }}</h2>
                <div class="text-8xl font-bold md:text-9xl">
                    {{ team === 'a' ? currentGame.team_a_score : currentGame.team_b_score }}
                </div>

                <div v-if="(team === 'a' ? match.team_a.players : match.team_b.players)?.length" class="mt-6 space-y-2 text-left">
                    <div
                        v-for="player in (team === 'a' ? match.team_a.players : match.team_b.players)"
                        :key="player.id"
                        class="flex items-center justify-between gap-3 rounded-md bg-black/20 px-3 py-2.5"
                    >
                        <span class="truncate text-sm font-medium text-gray-100"><span class="mr-1 text-gray-400">#{{ player.pivot?.jersey_number ?? '—' }}</span>{{ player.name }}</span>
                        <span v-if="isBasketball" class="shrink-0 text-xs text-gray-300">
                            {{ statFor(player.id).points }}p · {{ statFor(player.id).assists }}a · {{ statFor(player.id).rebounds }}r
                        </span>
                        <span v-else class="shrink-0 text-xs text-gray-300">{{ statFor(player.id).points }} pts</span>
                    </div>
                </div>
                <p v-else class="mt-6 text-sm text-gray-400">No players registered</p>
            </section>
        </div>

        <!-- Previous games strip -->
        <div v-if="games.length > 1" class="mt-10 flex gap-6 text-sm text-gray-400">
            <div
                v-for="game in games"
                :key="game.id ?? game.game_number"
                class="flex flex-col items-center px-4 py-2 rounded-lg"
                :class="game.game_number === currentGame.game_number ? 'bg-white/5 text-gray-200' : ''"
            >
                <span class="text-xs uppercase tracking-wide mb-1">Game {{ game.game_number }}</span>
                <span class="font-semibold text-base">
                    {{ game.team_a_score }} – {{ game.team_b_score }}
                </span>
            </div>
        </div>

    </div>
</template>