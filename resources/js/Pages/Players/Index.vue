<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    players: Array,
});
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Players
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                    <div v-if="players.length === 0" class="text-gray-500">
                        No players registered yet.
                    </div>

                    <ul v-else class="divide-y divide-gray-200">
                        <li
                            v-for="player in players"
                            :key="player.id"
                            class="py-3 flex justify-between items-center text-sm"
                        >
                            <Link :href="route('players.show', player.id)" class="text-indigo-600 hover:underline font-medium">
                                {{ player.name }}
                            </Link>
                            <span class="text-gray-400">Rating: {{ player.rating }}</span>
                        </li>
                    </ul>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>