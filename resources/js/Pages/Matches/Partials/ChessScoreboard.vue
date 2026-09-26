<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({ match: Object });

const games = computed(() => props.match.games ?? []);
const bestOf = computed(() => props.match.tournament.best_of ?? 3);
const wins = (teamId) => games.value.filter(g => g.winner_team_id === teamId).length;

const declareResult = (result) => {
    router.post(route('matches.chess-result', props.match.id), { result }, { preserveScroll: true });
};
</script>

<template>
    <div class="overflow-hidden rounded-xl bg-white shadow-[0px_14px_34px_0px_rgba(15,23,42,0.06)] ring-1 ring-slate-900/5 p-8">

        <p class="text-center text-xs font-medium uppercase tracking-wide text-slate-400 mb-6">
            Game {{ games.length || 1 }} of {{ bestOf }} · first to {{ Math.ceil(bestOf / 2) }} wins
        </p>

        <div class="grid grid-cols-2 gap-8 text-center mb-8">
            <div v-for="team in ['a', 'b']" :key="team">
                <h3 class="text-sm font-semibold text-slate-700 mb-2">{{ team === 'a' ? match.team_a.name : match.team_b.name }}</h3>
                <div class="text-5xl font-bold text-slate-900">{{ wins(team === 'a' ? match.team_a_id : match.team_b_id) }}</div>
                <p class="text-xs text-slate-400 mt-1">games won</p>
            </div>
        </div>

        <div v-if="match.status !== 'completed'" class="flex justify-center gap-3">
            <button @click="declareResult('a')" class="rounded-md bg-blue-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-400">{{ match.team_a.name }} Wins</button>
            <button @click="declareResult('draw')" class="rounded-md bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-200">Draw</button>
            <button @click="declareResult('b')" class="rounded-md bg-blue-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-400">{{ match.team_b.name }} Wins</button>
        </div>

        <div v-if="games.length" class="mt-8 flex justify-center gap-2 flex-wrap">
            <span v-for="g in games" :key="g.id" class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-600">
                Game {{ g.game_number }}: {{ g.is_draw ? 'Draw' : (g.winner_team_id === match.team_a_id ? match.team_a.name : g.winner_team_id ? match.team_b.name : '—') }}
            </span>
        </div>
    </div>
</template>