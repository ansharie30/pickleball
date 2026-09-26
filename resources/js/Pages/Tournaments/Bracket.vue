<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    division: Object,
    rounds: Object,
});
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ division.name }} — Bracket</h1>
        </template>

        <div class="py-8 sm:py-10">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">

                <div
                    v-if="Object.keys(rounds).length === 0"
                    class="flex flex-col items-center justify-center rounded-xl bg-white py-16 text-center shadow-[0px_14px_34px_0px_rgba(15,23,42,0.06)] ring-1 ring-slate-900/5"
                >
                    <p class="text-sm text-slate-500">No matches generated yet.</p>
                </div>

                <div v-else class="flex gap-6 overflow-x-auto pb-6">
                    <div
                        v-for="(matches, roundName) in rounds"
                        :key="roundName"
                        class="flex-shrink-0 w-64"
                    >
                        <h3 class="mb-4 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">{{ roundName }}</h3>

                        <div class="space-y-5">
                            <div
                                v-for="match in matches"
                                :key="match.id"
                                class="overflow-hidden rounded-lg bg-white shadow-[0px_14px_34px_0px_rgba(15,23,42,0.06)] ring-1 ring-slate-900/5"
                            >
                                <Link
                                    :href="route('matches.show', match.id)"
                                    class="block px-3 py-2.5 text-sm transition hover:bg-slate-50"
                                    :class="match.winner_team_id === match.team_a_id ? 'bg-emerald-50 font-semibold text-emerald-700' : 'text-slate-700'"
                                >
                                    {{ match.team_a?.name ?? 'TBD' }}
                                </Link>
                                <div class="border-t border-slate-100"></div>
                                <Link
                                    :href="route('matches.show', match.id)"
                                    class="block px-3 py-2.5 text-sm transition hover:bg-slate-50"
                                    :class="match.winner_team_id === match.team_b_id ? 'bg-emerald-50 font-semibold text-emerald-700' : 'text-slate-700'"
                                >
                                    {{ match.team_b?.name ?? 'TBD' }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <Link
                    :href="route('tournaments.show', division.tournament_id)"
                    class="mt-6 inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700 transition"
                >
                    ← Back to Tournament
                </Link>

            </div>
        </div>
    </AuthenticatedLayout>
</template>