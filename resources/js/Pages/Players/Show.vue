<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    player: Object,
    stats: Object,
});
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ player.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white rounded-lg shadow-sm p-5">
                        <p class="text-sm text-gray-500">Rating</p>
                        <p class="text-3xl font-bold mt-1">{{ player.rating }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-5">
                        <p class="text-sm text-gray-500">Wins</p>
                        <p class="text-3xl font-bold mt-1 text-green-600">{{ stats.wins }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-5">
                        <p class="text-sm text-gray-500">Losses</p>
                        <p class="text-3xl font-bold mt-1 text-red-500">{{ stats.losses }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-5">
                        <p class="text-sm text-gray-500">Win Rate</p>
                        <p class="text-3xl font-bold mt-1">{{ stats.win_rate }}%</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium mb-4">Teams</h3>
                    <ul class="divide-y divide-gray-200">
                        <li
                            v-for="team in player.teams"
                            :key="team.id"
                            class="py-2 text-sm text-gray-600"
                        >
                            {{ team.name }}
                        </li>
                    </ul>
                </div>

                <Link
                    :href="route('players.index')"
                    class="text-indigo-600 hover:underline text-sm"
                >
                    ← Back to Players
                </Link>

            </div>
        </div>
    </AuthenticatedLayout>
</template>