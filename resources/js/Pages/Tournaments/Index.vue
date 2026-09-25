<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    tournaments: Array,
});
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tournaments
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium">All Tournaments</h3>
                        <Link
                            :href="route('tournaments.create')"
                            class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700"
                        >
                            + New Tournament
                        </Link>
                    </div>

                    <div v-if="tournaments.length === 0" class="text-gray-500">
                        No tournaments yet.
                    </div>

                    <ul v-else class="divide-y divide-gray-200">
                        <li
                            v-for="tournament in tournaments"
                            :key="tournament.id"
                            class="py-4 flex justify-between items-center"
                        >
                            <div>
                                <Link
                                    :href="route('tournaments.show', tournament.id)"
                                    class="text-indigo-600 font-medium hover:underline"
                                >
                                    {{ tournament.name }}
                                </Link>
                                <p class="text-sm text-gray-500">
                                    {{ tournament.format }} — {{ tournament.status }}
                                </p>
                            </div>
                            <span class="text-sm text-gray-400">
                                {{ tournament.start_date }}
                            </span>
                        </li>
                    </ul>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>