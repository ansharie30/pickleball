<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    match: Object,
    availableTeams: Array,
});

const form = useForm({
    team_a_id: props.match.team_a_id,
    team_b_id: props.match.team_b_id,
    scheduled_at: props.match.scheduled_at,
    status: props.match.status,
});

const submit = () => {
    form.patch(route('matches.update', props.match.id));
};

const deleteMatch = () => {
    if (confirm('Delete this match? This cannot be undone.')) {
        form.delete(route('matches.destroy', props.match.id));
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Edit Match</h1>
        </template>

        <div class="py-8 sm:py-10">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-xl bg-white shadow-[0px_14px_34px_0px_rgba(15,23,42,0.06)] ring-1 ring-slate-900/5">
                    <div class="border-b border-slate-100 px-6 py-5 sm:px-8">
                        <h2 class="text-base font-semibold text-slate-900">Match details</h2>
                        <p class="mt-1 text-sm text-slate-500">Update the teams, schedule, or status for this match.</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6 px-6 py-6 sm:px-8">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Team A</label>
                            <select
                                v-model="form.team_a_id"
                                class="mt-2 block w-full rounded-md border-slate-200 bg-slate-50/50 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option v-for="team in availableTeams" :key="team.id" :value="team.id">
                                    {{ team.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.team_a_id" class="mt-2 text-sm text-red-600">
                                {{ form.errors.team_a_id }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Team B</label>
                            <select
                                v-model="form.team_b_id"
                                class="mt-2 block w-full rounded-md border-slate-200 bg-slate-50/50 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option v-for="team in availableTeams" :key="team.id" :value="team.id">
                                    {{ team.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.team_b_id" class="mt-2 text-sm text-red-600">
                                {{ form.errors.team_b_id }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Scheduled Time</label>
                            <input
                                v-model="form.scheduled_at"
                                type="datetime-local"
                                class="mt-2 block w-full rounded-md border-slate-200 bg-slate-50/50 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Status</label>
                            <select
                                v-model="form.status"
                                class="mt-2 block w-full rounded-md border-slate-200 bg-slate-50/50 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="scheduled">Scheduled</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>

                        <div class="flex items-center justify-between border-t border-slate-100 pt-5">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center rounded-md bg-blue-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {{ form.processing ? 'Saving...' : 'Save Changes' }}
                            </button>

                            <button
                                type="button"
                                @click="deleteMatch"
                                class="text-sm font-medium text-red-500 hover:text-red-600 transition"
                            >
                                Delete Match
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>