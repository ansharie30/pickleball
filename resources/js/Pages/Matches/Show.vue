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

const startMatch = () => {
    router.patch(route('matches.start', props.match.id), {}, {
        preserveScroll: true,
        onError: (errors) => {
            console.error('match start failed', errors);
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

    QRCode.toCanvas(qrCanvas.value, publicUrl, { width: 72 });
});

onUnmounted(() => {
    if (window.Echo) {
        window.Echo.leaveChannel('match.' + props.match.id);
    }
});

const statusConfig = {
    scheduled: { label: 'Scheduled', class: 'bg-slate-100 text-slate-500' },
    in_progress: { label: 'In Progress', class: 'bg-amber-100 text-amber-700' },
    completed: { label: 'Completed', class: 'bg-emerald-100 text-emerald-700' },
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Live Match</h1>
        </template>

        <div class="py-8 sm:py-10">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">

                <div class="mb-4 flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="shrink-0 rounded-lg bg-white p-1.5 shadow-[0px_14px_34px_0px_rgba(15,23,42,0.06)] ring-1 ring-slate-900/5">
                            <canvas ref="qrCanvas" class="block h-[72px] w-[72px]"></canvas>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium"
                                    :class="statusConfig[match.status]?.class"
                                >
                                    {{ statusConfig[match.status]?.label ?? match.status }}
                                </span>
                                <span v-if="match.status === 'in_progress'" class="text-sm text-slate-500">
                                    Game {{ currentGame.game_number || 1 }}
                                </span>
                            </div>
                            <span v-if="match.status === 'completed'" class="mt-1 block text-sm font-semibold text-emerald-700">
                                Winner: {{ match.winner_team_id === match.team_a_id ? match.team_a.name : match.team_b.name }}
                            </span>
                            <p v-else class="mt-1 text-xs text-slate-400">Scan to view public scoreboard</p>
                        </div>
                    </div>

                    <Link
                        :href="route('matches.edit', match.id)"
                        class="shrink-0 text-sm font-medium text-slate-500 hover:text-slate-700 transition"
                    >
                        Edit Match
                    </Link>
                </div>

                <div v-if="match.status === 'scheduled'" class="mb-4 text-center">
                    <button
                        type="button"
                        @click="startMatch"
                        class="inline-flex items-center rounded-md bg-emerald-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-400 transition"
                    >
                        Start Match
                    </button>
                </div>

                <div class="overflow-hidden rounded-xl bg-white shadow-[0px_14px_34px_0px_rgba(15,23,42,0.06)] ring-1 ring-slate-900/5 p-8">

                    <div class="grid grid-cols-2 gap-8 text-center">

                        <div>
                            <h3 class="text-sm font-semibold text-slate-700 mb-4">{{ match.team_a.name }}</h3>
                            <div class="text-6xl font-bold text-slate-900 mb-6">
                                {{ currentGame.team_a_score }}
                            </div>
                            <div class="flex justify-center gap-2">
                                <button
                                    type="button"
                                    :disabled="match.status !== 'in_progress'"
                                    @click="score('a', 'decrement')"
                                    class="h-12 w-12 rounded-md bg-slate-100 text-xl text-slate-600 hover:bg-slate-200 transition disabled:cursor-not-allowed disabled:opacity-40"
                                >
                                    −
                                </button>
                                <button
                                    type="button"
                                    :disabled="match.status !== 'in_progress'"
                                    @click="score('a', 'increment')"
                                    class="h-12 w-12 rounded-md bg-blue-500 text-xl font-medium text-white hover:bg-blue-400 transition disabled:cursor-not-allowed disabled:opacity-40"
                                >
                                    +
                                </button>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-slate-700 mb-4">{{ match.team_b.name }}</h3>
                            <div class="text-6xl font-bold text-slate-900 mb-6">
                                {{ currentGame.team_b_score }}
                            </div>
                            <div class="flex justify-center gap-2">
                                <button
                                    type="button"
                                    :disabled="match.status !== 'in_progress'"
                                    @click="score('b', 'decrement')"
                                    class="h-12 w-12 rounded-md bg-slate-100 text-xl text-slate-600 hover:bg-slate-200 transition disabled:cursor-not-allowed disabled:opacity-40"
                                >
                                    −
                                </button>
                                <button
                                    type="button"
                                    :disabled="match.status !== 'in_progress'"
                                    @click="score('b', 'increment')"
                                    class="h-12 w-12 rounded-md bg-blue-500 text-xl font-medium text-white hover:bg-blue-400 transition disabled:cursor-not-allowed disabled:opacity-40"
                                >
                                    +
                                </button>
                            </div>
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
                        <span class="text-xs font-medium uppercase tracking-wide text-slate-400 mb-1">Game {{ game.game_number }}</span>
                        <span class="text-sm font-semibold text-slate-700">
                            {{ game.team_a_score }} – {{ game.team_b_score }}
                        </span>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>