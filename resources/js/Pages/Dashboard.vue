<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    stats: Object,
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div class="bg-white rounded-lg shadow-sm p-5">
                        <p class="text-sm text-gray-500">Tournaments</p>
                        <p class="text-3xl font-bold mt-1">{{ stats.total_tournaments }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-5">
                        <p class="text-sm text-gray-500">Divisions</p>
                        <p class="text-3xl font-bold mt-1">{{ stats.total_divisions }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-5">
                        <p class="text-sm text-gray-500">Teams</p>
                        <p class="text-3xl font-bold mt-1">{{ stats.total_teams }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-5">
                        <p class="text-sm text-gray-500">Courts</p>
                        <p class="text-3xl font-bold mt-1">{{ stats.total_courts }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-5">
                        <p class="text-sm text-gray-500">Matches In Progress</p>
                        <p class="text-3xl font-bold mt-1 text-yellow-600">{{ stats.matches_in_progress }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-5">
                        <p class="text-sm text-gray-500">Matches Completed</p>
                        <p class="text-3xl font-bold mt-1 text-green-600">{{ stats.matches_completed }}</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium mb-4">Quick Actions</h3>
                    <div class="flex flex-wrap gap-3">
                        <Link
                            :href="route('tournaments.create')"
                            class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 text-sm"
                        >
                            + New Tournament
                        </Link>
                        <Link
                            :href="route('courts.create')"
                            class="bg-gray-100 text-gray-700 px-4 py-2 rounded hover:bg-gray-200 text-sm"
                        >
                            + New Court
                        </Link>
                        <Link
                            :href="route('tournaments.index')"
                            class="bg-gray-100 text-gray-700 px-4 py-2 rounded hover:bg-gray-200 text-sm"
                        >
                            View All Tournaments
                        </Link>
                        <Link
                            :href="route('courts.index')"
                            class="bg-gray-100 text-gray-700 px-4 py-2 rounded hover:bg-gray-200 text-sm"
                        >
                            View All Courts
                        </Link>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>