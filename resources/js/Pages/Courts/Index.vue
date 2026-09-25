<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';

defineProps({
    courts: Array,
});

const statusColors = {
    available: 'bg-green-100 text-green-700',
    in_use: 'bg-yellow-100 text-yellow-700',
    reserved: 'bg-blue-100 text-blue-700',
    maintenance: 'bg-red-100 text-red-700',
};

const updateStatus = (courtId, status) => {
    router.patch(route('courts.status', courtId), { status }, { preserveScroll: true });
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Courts
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium">All Courts</h3>
                        <Link
                            :href="route('courts.create')"
                            class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700"
                        >
                            + New Court
                        </Link>
                    </div>

                    <div v-if="courts.length === 0" class="text-gray-500">
                        No courts yet.
                    </div>

                    <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div
                            v-for="court in courts"
                            :key="court.id"
                            class="border rounded-lg p-4"
                        >
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <p class="font-semibold">{{ court.name }}</p>
                                    <p class="text-xs text-gray-500">{{ court.venue?.name }}</p>
                                </div>
                                <span
                                    class="text-xs px-2 py-1 rounded"
                                    :class="statusColors[court.status]"
                                >
                                    {{ court.status }}
                                </span>
                            </div>

                            <select
                                :value="court.status"
                                @change="updateStatus(court.id, $event.target.value)"
                                class="mt-2 w-full text-sm border-gray-300 rounded"
                            >
                                <option value="available">Available</option>
                                <option value="in_use">In Use</option>
                                <option value="reserved">Reserved</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>