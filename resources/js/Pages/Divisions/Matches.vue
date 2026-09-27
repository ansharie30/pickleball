<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';

const props = defineProps({
    division: Object,
    matches: Array,
});

const toLocalDateTime = (value) => {
    if (!value) return '';

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '';

    const pad = (part) => String(part).padStart(2, '0');
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
};

const formatMatchDate = (value) => value ? new Date(value).toLocaleString(undefined, {
    month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit',
}) : null;

const scheduleForms = reactive({});

watch(() => props.matches, (matches) => {
    matches.forEach((match) => {
        if (!scheduleForms[match.id]) {
            scheduleForms[match.id] = useForm({ scheduled_at: toLocalDateTime(match.scheduled_at) });
        }
    });
}, { immediate: true });

const saveSchedule = (matchId) => {
    scheduleForms[matchId].patch(route('matches.schedule', matchId), {
        preserveScroll: true,
    });
};

const generateMatches = () => {
    router.post(route('divisions.generate-matches', props.division.id), {}, {
        preserveScroll: true,
    });
};

const statusColors = {
    scheduled: 'bg-slate-100 text-slate-500',
    in_progress: 'bg-amber-100 text-amber-700',
    completed: 'bg-emerald-100 text-emerald-700',
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ division.name }} — Matches</h1>
        </template>

        <div class="py-8 sm:py-10">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">

                <div class="mb-5 flex items-center justify-between gap-4">
                    <p class="text-sm text-slate-500">{{ matches.length }} matches</p>
                    <button
                        type="button"
                        @click="generateMatches"
                        :disabled="matches.length > 0"
                        class="inline-flex items-center rounded-md bg-blue-500 px-3 py-2 text-xs font-semibold text-white transition hover:bg-blue-400 disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-500"
                    >
                        {{ matches.length > 0 ? 'Matches Generated' : 'Generate Matches' }}
                    </button>
                </div>

                <div
                    v-if="matches.length === 0"
                    class="flex flex-col items-center justify-center rounded-xl bg-white py-16 text-center shadow-[0px_14px_34px_0px_rgba(15,23,42,0.06)] ring-1 ring-slate-900/5"
                >
                    <p class="text-sm text-slate-500">No matches generated yet for this division.</p>
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="match in matches"
                        :key="match.id"
                        class="rounded-xl bg-white shadow-[0px_14px_34px_0px_rgba(15,23,42,0.06)] ring-1 ring-slate-900/5 p-5"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <Link :href="route('matches.show', match.id)" class="group min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-900 group-hover:text-blue-600">
                                    {{ match.team_a?.name ?? 'TBD' }} <span class="text-slate-400 font-normal">vs</span> {{ match.team_b?.name ?? 'TBD' }}
                                </p>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    <span v-if="match.round">{{ match.round }}</span>
                                    <span v-if="match.court"> · {{ match.court.name }}</span>
                                </p>
                            </Link>

                            <span
                                class="shrink-0 inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                                :class="statusColors[match.status]"
                            >
                                {{ match.status.replace('_', ' ') }}
                            </span>
                        </div>

                        <div class="mt-4 flex flex-col gap-2 border-t border-slate-100 pt-3 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-xs text-slate-500">
                                <span v-if="formatMatchDate(match.scheduled_at)">
                                    Scheduled for <span class="font-medium text-slate-700">{{ formatMatchDate(match.scheduled_at) }}</span>
                                </span>
                                <span v-else class="text-slate-400">Not scheduled yet</span>
                            </p>

                            <form class="flex items-center gap-2" @submit.prevent="saveSchedule(match.id)">
                                <label class="sr-only" :for="`schedule-${match.id}`">Schedule date and time</label>
                                <input
                                    :id="`schedule-${match.id}`"
                                    v-model="scheduleForms[match.id].scheduled_at"
                                    type="datetime-local"
                                    class="rounded-md border-slate-200 bg-slate-50/50 text-xs text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                                <button
                                    type="submit"
                                    :disabled="scheduleForms[match.id].processing"
                                    class="inline-flex items-center rounded-md bg-blue-500 px-3 py-2 text-xs font-semibold text-white transition hover:bg-blue-400 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    Save
                                </button>
                            </form>
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