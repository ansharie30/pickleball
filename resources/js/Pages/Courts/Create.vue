<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';

defineProps({
    venues: Array,
});

const form = useForm({
    venue_id: '',
    name: '',
});

const submit = () => {
    form.post(route('courts.store'));
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                New Court
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Venue</label>
                            <select
                                v-model="form.venue_id"
                                class="mt-1 block w-full border-gray-300 rounded-md"
                            >
                                <option value="" disabled>Select a venue</option>
                                <option v-for="venue in venues" :key="venue.id" :value="venue.id">
                                    {{ venue.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.venue_id" class="text-red-600 text-sm">
                                {{ form.errors.venue_id }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Court Name</label>
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="e.g. Court 3"
                                class="mt-1 block w-full border-gray-300 rounded-md"
                            />
                            <div v-if="form.errors.name" class="text-red-600 text-sm">
                                {{ form.errors.name }}
                            </div>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700"
                        >
                            Create Court
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>