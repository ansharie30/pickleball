<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    venues: Array,
});
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Venues
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium">All Venues</h3>
                        <Link
                            :href="route('venues.create')"
                            class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700"
                        >
                            + New Venue
                        </Link>
                    </div>

                    <div v-if="venues.length === 0" class="text-gray-500">
                        No venues yet.
                    </div>

                    <ul v-else class="divide-y divide-gray-200">
                        <li
                            v-for="venue in venues"
                            :key="venue.id"
                            class="py-3 flex justify-between items-center text-sm"
                        >
                            <div>
                                <p class="font-medium">{{ venue.name }}</p>
                                <p class="text-xs text-gray-500">{{ venue.address }}</p>
                            </div>
                            <span class="text-gray-400">{{ venue.courts_count }} courts</span>
                        </li>
                    </ul>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>