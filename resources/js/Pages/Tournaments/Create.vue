<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    format: 'single_elimination',
    start_date: '',
    end_date: '',
});

const submit = () => {
    form.post(route('tournaments.store'));
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create Tournament
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full border-gray-300 rounded-md"
                            />
                            <div v-if="form.errors.name" class="text-red-600 text-sm">
                                {{ form.errors.name }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Format</label>
                            <select
                                v-model="form.format"
                                class="mt-1 block w-full border-gray-300 rounded-md"
                            >
                                <option value="single_elimination">Single Elimination</option>
                                <option value="double_elimination">Double Elimination</option>
                                <option value="round_robin">Round Robin</option>
                                <option value="pool_play">Pool Play</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input
                                v-model="form.start_date"
                                type="date"
                                class="mt-1 block w-full border-gray-300 rounded-md"
                            />
                            <div v-if="form.errors.start_date" class="text-red-600 text-sm">
                                {{ form.errors.start_date }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">End Date (optional)</label>
                            <input
                                v-model="form.end_date"
                                type="date"
                                class="mt-1 block w-full border-gray-300 rounded-md"
                            />
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700"
                        >
                            Create Tournament
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>