<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    match: Object,
});

const games = ref(props.match.games ?? []);

const currentGame = ref(
    games.value.length > 0
        ? games.value[games.value.length - 1]
        : { team_a_score: 0, team_b_score: 0, game_number: 1 }
);

const status = ref(props.match.status);
const winnerTeamId = ref(props.match.winner_team_id);

let channel = null;

onMounted(() => {
    if (window.Echo) {
        channel = window.Echo.channel('match.' + props.match.id);
        channel.listen('.score.updated', (e) => {
            if (e.match) {
                if (e.match.games && e.match.games.length > 0) {
                    games.value = e.match.games;
                    currentGame.value = e.match.games[e.match.games.length - 1];
                }
                status.value = e.match.status;
                winnerTeamId.value = e.match.winner_team_id;
            }
        });
    }
});

onUnmounted(() => {
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
        <div v-else class="mb-8 text-gray-400 text-lg">
            Game {{ currentGame.game_number || 1 }}
        </div>

        <div class="grid grid-cols-2 gap-12 w-full max-w-2xl text-center">
            <div>
                <h2 class="text-xl md:text-2xl font-medium mb-4">{{ match.team_a.name }}</h2>
                <div class="text-8xl md:text-9xl font-bold">
                    {{ currentGame.team_a_score }}
                </div>
            </div>
            <div>
                <h2 class="text-xl md:text-2xl font-medium mb-4">{{ match.team_b.name }}</h2>
                <div class="text-8xl md:text-9xl font-bold">
                    {{ currentGame.team_b_score }}
                </div>
            </div>
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