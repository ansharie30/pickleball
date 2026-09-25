<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    division: Object,
});

const form = useForm({
    team_name: '',
    player_one_name: '',
    player_two_name: '',
});

const submit = () => {
    form.post(route('teams.store', props.division.id));
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Register Team — {{ division.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Player 1 Name</label>
                            <input
                                v-model="form.player_one_name"
                                type="text"
                                class="mt-1 block w-full border-gray-300 rounded-md"
                            />
                            <div v-if="form.errors.player_one_name" class="text-red-600 text-sm">
                                {{ form.errors.player_one_name }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Player 2 Name <span class="text-gray-400">(leave blank for singles)</span>
                            </label>
                            <input
                                v-model="form.player_two_name"
                                type="text"
                                class="mt-1 block w-full border-gray-300 rounded-md"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Team Name <span class="text-gray-400">(optional — auto-generated if blank)</span>
                            </label>
                            <input
                                v-model="form.team_name"
                                type="text"
                                placeholder="e.g. The Dinkers"
                                class="mt-1 block w-full border-gray-300 rounded-md"
                            />
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700"
                        >
                            Register Team
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>