<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    division: Object,
    standings: Array,
});
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ division.name }} — Standings
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b">
                                <th class="py-2">#</th>
                                <th class="py-2">Team</th>
                                <th class="py-2 text-center">Wins</th>
                                <th class="py-2 text-center">Losses</th>
                                <th class="py-2 text-center">Played</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(row, index) in standings"
                                :key="row.team_id"
                                class="border-b last:border-0"
                            >
                                <td class="py-3 font-medium">{{ index + 1 }}</td>
                                <td class="py-3">{{ row.team_name }}</td>
                                <td class="py-3 text-center font-semibold text-green-600">{{ row.wins }}</td>
                                <td class="py-3 text-center text-red-500">{{ row.losses }}</td>
                                <td class="py-3 text-center text-gray-400">{{ row.matches_played }}</td>
                            </tr>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>