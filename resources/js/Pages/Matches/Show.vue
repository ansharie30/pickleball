<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import QRCode from 'qrcode';

const props = defineProps({
    match: Object,
});

const qrCanvas = ref(null);
const publicUrl = window.location.origin + '/watch/' + props.match.id;

const games = ref(props.match.games ?? []);

const currentGame = ref(
    games.value.length > 0
        ? games.value[games.value.length - 1]
        : { team_a_score: 0, team_b_score: 0 }
);

watch(
    () => props.match.games,
    (newGames) => {
        if (newGames && newGames.length > 0) {
            games.value = newGames;
            currentGame.value = newGames[newGames.length - 1];
        }
    },
    { deep: true }
);

const score = (team, action) => {
    router.post(route('matches.score', props.match.id), {
        team,
        action,
    }, {
        preserveScroll: true,
        onError: (errors) => {
            console.error('score update failed', errors);
        },
    });
};

let channel = null;

onMounted(() => {
    if (window.Echo) {
        channel = window.Echo.channel('match.' + props.match.id);
        channel.listen('.score.updated', (e) => {
            if (e.match && e.match.games && e.match.games.length > 0) {
                games.value = e.match.games;
                currentGame.value = e.match.games[e.match.games.length - 1];
            }
        });
    } else {
        console.warn('window.Echo is not defined');
    }

    QRCode.toCanvas(qrCanvas.value, publicUrl, { width: 160 });
});

onUnmounted(() => {
    if (window.Echo) {
        window.Echo.leaveChannel('match.' + props.match.id);
    }
});
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Live Match
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

                <div class="flex justify-between items-center mb-4">
                    <div v-if="match.status === 'completed'" class="text-sm text-green-700 font-semibold">
                        Match Complete — Winner: {{ match.winner_team_id === match.team_a_id ? match.team_a.name : match.team_b.name }}
                    </div>
                    <div v-else class="text-sm text-gray-500">
                        Game {{ currentGame.game_number || 1 }}
                    </div>

                    <Link
                        :href="route('matches.edit', match.id)"
                        class="text-sm text-gray-500 hover:text-gray-700 hover:underline"
                    >
                        Edit Match
                    </Link>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8">

                    <div class="grid grid-cols-2 gap-8 text-center">

                        <div>
                            <h3 class="text-lg font-medium mb-4">{{ match.team_a.name }}</h3>
                            <div class="text-6xl font-bold mb-6">
                                {{ currentGame.team_a_score }}
                            </div>
                            <div class="flex justify-center gap-2">
                                <button
                                    type="button"
                                    :disabled="match.status === 'completed'"
                                    @click="score('a', 'decrement')"
                                    class="bg-gray-200 hover:bg-gray-300 w-12 h-12 rounded text-xl disabled:opacity-40 disabled:cursor-not-allowed"
                                >
                                    −
                                </button>
                                <button
                                    type="button"
                                    :disabled="match.status === 'completed'"
                                    @click="score('a', 'increment')"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white w-12 h-12 rounded text-xl disabled:opacity-40 disabled:cursor-not-allowed"
                                >
                                    +
                                </button>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium mb-4">{{ match.team_b.name }}</h3>
                            <div class="text-6xl font-bold mb-6">
                                {{ currentGame.team_b_score }}
                            </div>
                            <div class="flex justify-center gap-2">
                                <button
                                    type="button"
                                    :disabled="match.status === 'completed'"
                                    @click="score('b', 'decrement')"
                                    class="bg-gray-200 hover:bg-gray-300 w-12 h-12 rounded text-xl disabled:opacity-40 disabled:cursor-not-allowed"
                                >
                                    −
                                </button>
                                <button
                                    type="button"
                                    :disabled="match.status === 'completed'"
                                    @click="score('b', 'increment')"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white w-12 h-12 rounded text-xl disabled:opacity-40 disabled:cursor-not-allowed"
                                >
                                    +
                                </button>
                            </div>
                        </div>

                    </div>

                </div>

                <div v-if="games.length > 1" class="mt-6 flex justify-center gap-4">
                    <div
                        v-for="game in games"
                        :key="game.id ?? game.game_number"
                        class="flex flex-col items-center px-4 py-2 rounded-lg border"
                        :class="game.game_number === currentGame.game_number ? 'bg-indigo-50 border-indigo-200' : 'border-gray-200'"
                    >
                        <span class="text-xs uppercase tracking-wide text-gray-400 mb-1">Game {{ game.game_number }}</span>
                        <span class="font-semibold text-sm text-gray-700">
                            {{ game.team_a_score }} – {{ game.team_b_score }}
                        </span>
                    </div>
                </div>

                <div class="text-center mt-6">
                    <canvas ref="qrCanvas" class="mx-auto"></canvas>
                    <p class="text-xs text-gray-400 mt-2">Scan to view public scoreboard</p>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>