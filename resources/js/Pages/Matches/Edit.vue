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
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Match
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Team A</label>
                            <select
                                v-model="form.team_a_id"
                                class="mt-1 block w-full border-gray-300 rounded-md"
                            >
                                <option v-for="team in availableTeams" :key="team.id" :value="team.id">
                                    {{ team.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.team_a_id" class="text-red-600 text-sm">
                                {{ form.errors.team_a_id }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Team B</label>
                            <select
                                v-model="form.team_b_id"
                                class="mt-1 block w-full border-gray-300 rounded-md"
                            >
                                <option v-for="team in availableTeams" :key="team.id" :value="team.id">
                                    {{ team.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.team_b_id" class="text-red-600 text-sm">
                                {{ form.errors.team_b_id }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Scheduled Time</label>
                            <input
                                v-model="form.scheduled_at"
                                type="datetime-local"
                                class="mt-1 block w-full border-gray-300 rounded-md"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select
                                v-model="form.status"
                                class="mt-1 block w-full border-gray-300 rounded-md"
                            >
                                <option value="scheduled">Scheduled</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>

                        <div class="flex justify-between items-center pt-2">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700"
                            >
                                Save Changes
                            </button>

                            <button
                                type="button"
                                @click="deleteMatch"
                                class="text-red-600 hover:underline text-sm"
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