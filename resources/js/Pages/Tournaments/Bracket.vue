<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    division: Object,
    rounds: Object,
});
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ division.name }} — Bracket
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div v-if="Object.keys(rounds).length === 0" class="text-gray-500">
                    No matches generated yet.
                </div>

                <div v-else class="flex gap-8 overflow-x-auto pb-6">
                    <div
                        v-for="(matches, roundName) in rounds"
                        :key="roundName"
                        class="flex-shrink-0 w-64"
                    >
                        <h3 class="font-semibold text-gray-700 mb-4 text-center">{{ roundName }}</h3>

                        <div class="space-y-6">
                            <div
                                v-for="match in matches"
                                :key="match.id"
                                class="bg-white border rounded-lg p-3 text-sm"
                            >
                                <Link
                                    :href="route('matches.show', match.id)"
                                    class="block py-1 px-2 rounded"
                                    :class="match.winner_team_id === match.team_a_id ? 'bg-green-50 font-semibold' : ''"
                                >
                                    {{ match.team_a?.name ?? 'TBD' }}
                                </Link>
                                <div class="border-t my-1"></div>
                                <Link
                                    :href="route('matches.show', match.id)"
                                    class="block py-1 px-2 rounded"
                                    :class="match.winner_team_id === match.team_b_id ? 'bg-green-50 font-semibold' : ''"
                                >
                                    {{ match.team_b?.name ?? 'TBD' }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <Link
                    :href="route('tournaments.show', division.tournament_id)"
                    class="text-indigo-600 hover:underline text-sm mt-6 inline-block"
                >
                    ← Back to Tournament
                </Link>

            </div>
        </div>
    </AuthenticatedLayout>
</template>