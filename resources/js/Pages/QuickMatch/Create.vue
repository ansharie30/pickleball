<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    court: Object,
});

const form = useForm({
    team_a_name: '',
    team_b_name: '',
});

const submit = () => {
    form.post(route('quick-match.store', props.court.id));
};
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center p-6">
        <div class="bg-white rounded-lg shadow-sm p-6 w-full max-w-md">

            <h1 class="text-xl font-semibold mb-1">Start a Game</h1>
            <p class="text-sm text-gray-500 mb-6">{{ court.name }}</p>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Player/Team 1 Name</label>
                    <input
                        v-model="form.team_a_name"
                        type="text"
                        placeholder="e.g. Mike & Sarah"
                        class="mt-1 block w-full border-gray-300 rounded-md"
                    />
                    <div v-if="form.errors.team_a_name" class="text-red-600 text-sm">
                        {{ form.errors.team_a_name }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Player/Team 2 Name</label>
                    <input
                        v-model="form.team_b_name"
                        type="text"
                        placeholder="e.g. John & Amy"
                        class="mt-1 block w-full border-gray-300 rounded-md"
                    />
                    <div v-if="form.errors.team_b_name" class="text-red-600 text-sm">
                        {{ form.errors.team_b_name }}
                    </div>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full bg-indigo-600 text-white px-4 py-3 rounded-lg font-medium hover:bg-indigo-700"
                >
                    Start Scoring
                </button>
            </form>

        </div>
    </div>
</template>