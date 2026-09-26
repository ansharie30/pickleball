<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PointScoreboard from './Partials/PointScoreboard.vue';
import BasketballScoreboard from './Partials/BasketballScoreboard.vue';
import ChessScoreboard from './Partials/ChessScoreboard.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({ match: Object });

const scoringType = props.match.tournament?.sport?.scoring_type ?? 'point_based';
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Live Match</h1>
                <Link :href="route('matches.edit', match.id)" class="text-sm font-medium text-slate-500 hover:text-slate-700">Edit Match</Link>
            </div>
        </template>

        <div class="py-8 sm:py-10">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <PointScoreboard v-if="scoringType === 'point_based' || scoringType === 'set_based'" :match="match" />
                <BasketballScoreboard v-else-if="scoringType === 'timed_period'" :match="match" />
                <ChessScoreboard v-else-if="scoringType === 'result_based'" :match="match" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>