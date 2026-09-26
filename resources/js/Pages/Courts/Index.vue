<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    courts: Array,
    venues: Array,
});

const statusConfig = {
    available: { label: 'Available', class: 'bg-emerald-100 text-emerald-700' },
    in_use: { label: 'In Use', class: 'bg-amber-100 text-amber-700' },
    reserved: { label: 'Reserved', class: 'bg-blue-100 text-blue-700' },
    maintenance: { label: 'Maintenance', class: 'bg-red-100 text-red-700' },
};

const updateStatus = (courtId, status) => {
    router.patch(route('courts.status', courtId), { status }, { preserveScroll: true });
};

const showCreateModal = ref(false);

const form = useForm({
    venue_id: '',
    name: '',
});

const openModal = () => {
    form.reset();
    form.clearErrors();
    showCreateModal.value = true;
};

const closeModal = () => {
    showCreateModal.value = false;
};

const submit = () => {
    form.post(route('courts.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
        },
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Courts</h1>
                <button
                    @click="openModal"
                    class="inline-flex items-center rounded-md bg-blue-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-400 transition"
                >
                    + New Court
                </button>
            </div>
        </template>

        <div class="py-8 sm:py-10">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

                <div
                    v-if="courts.length === 0"
                    class="flex flex-col items-center justify-center rounded-xl bg-white py-16 text-center shadow-[0px_14px_34px_0px_rgba(15,23,42,0.06)] ring-1 ring-slate-900/5"
                >
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
                        <svg class="h-6 w-6 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4h16v16H4zM4 12h16M12 4v16" />
                        </svg>
                    </div>
                    <p class="mt-4 text-sm font-medium text-slate-900">No courts yet</p>
                    <p class="mt-1 text-sm text-slate-500">Add a court to a venue so it can be used for matches.</p>
                </div>

                <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="court in courts"
                        :key="court.id"
                        class="rounded-xl bg-white p-5 shadow-[0px_14px_34px_0px_rgba(15,23,42,0.06)] ring-1 ring-slate-900/5"
                    >
                        <div class="mb-3 flex items-start justify-between">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-900">{{ court.name }}</p>
                                <p class="truncate text-xs text-slate-500">{{ court.venue?.name }}</p>
                            </div>
                            <span
                                class="shrink-0 inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium"
                                :class="statusConfig[court.status]?.class"
                            >
                                {{ statusConfig[court.status]?.label ?? court.status }}
                            </span>
                        </div>

                        <select
                            :value="court.status"
                            @change="updateStatus(court.id, $event.target.value)"
                            class="mt-2 block w-full rounded-md border-slate-200 bg-slate-50/50 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500"
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

        <Modal :show="showCreateModal" @close="closeModal" max-width="lg">
            <div class="p-6">
                <h2 class="text-base font-semibold text-slate-900 mb-1">New Court</h2>
                <p class="text-sm text-slate-500 mb-5">Add a court to a venue so it can be used for matches.</p>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-semibold text-slate-700">Venue</label>
                            <Link :href="route('venues.create')" class="text-xs font-medium text-blue-500 hover:text-blue-400 hover:underline">
                                + New Venue
                            </Link>
                        </div>
                        <select
                            v-model="form.venue_id"
                            class="mt-2 block w-full rounded-md border-slate-200 bg-slate-50/50 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="" disabled>Select a venue</option>
                            <option v-for="venue in venues" :key="venue.id" :value="venue.id">
                                {{ venue.name }}
                            </option>
                        </select>
                        <div v-if="form.errors.venue_id" class="mt-2 text-sm text-red-600">
                            {{ form.errors.venue_id }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Court Name</label>
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="e.g. Court 3"
                            class="mt-2 block w-full rounded-md border-slate-200 bg-slate-50/50 text-sm text-slate-700 placeholder:text-slate-400 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        />
                        <div v-if="form.errors.name" class="mt-2 text-sm text-red-600">
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                        <button
                            type="button"
                            @click="closeModal"
                            class="rounded-md px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100 transition"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center rounded-md bg-blue-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-400 transition disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{ form.processing ? 'Creating...' : 'Create Court' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>