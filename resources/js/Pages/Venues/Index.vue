<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    venues: Array,
});

const showCreateModal = ref(false);

const form = useForm({
    name: '',
    address: '',
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
    form.post(route('venues.store'), {
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
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Venues</h1>
                <button
                    @click="openModal"
                    class="inline-flex items-center rounded-md bg-blue-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-400 transition"
                >
                    + New Venue
                </button>
            </div>
        </template>

        <div class="py-8 sm:py-10">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">

                <div
                    v-if="venues.length === 0"
                    class="flex flex-col items-center justify-center rounded-xl bg-white py-16 text-center shadow-[0px_14px_34px_0px_rgba(15,23,42,0.06)] ring-1 ring-slate-900/5"
                >
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
                        <svg class="h-6 w-6 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 9h1m4 0h1m-6 4h1m4 0h1m-6 4h1m4 0h1" />
                        </svg>
                    </div>
                    <p class="mt-4 text-sm font-medium text-slate-900">No venues yet</p>
                    <p class="mt-1 text-sm text-slate-500">Add a venue so you can create courts for it.</p>
                </div>

                <div v-else class="overflow-hidden rounded-xl bg-white shadow-[0px_14px_34px_0px_rgba(15,23,42,0.06)] ring-1 ring-slate-900/5">
                    <div
                        v-for="(venue, index) in venues"
                        :key="venue.id"
                        class="flex items-center justify-between gap-4 px-6 py-4"
                        :class="index !== venues.length - 1 ? 'border-b border-slate-100' : ''"
                    >
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ venue.name }}</p>
                            <p v-if="venue.address" class="mt-0.5 truncate text-xs text-slate-500">{{ venue.address }}</p>
                        </div>
                        <span class="shrink-0 inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500">
                            {{ venue.courts_count }} {{ venue.courts_count === 1 ? 'court' : 'courts' }}
                        </span>
                    </div>
                </div>

            </div>
        </div>

        <Modal :show="showCreateModal" @close="closeModal" max-width="lg">
            <div class="p-6">
                <h2 class="text-base font-semibold text-slate-900 mb-1">New Venue</h2>
                <p class="text-sm text-slate-500 mb-5">Add a venue so you can create courts for it.</p>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Venue Name</label>
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="e.g. Sunrise Pickleball Courts"
                            class="mt-2 block w-full rounded-md border-slate-200 bg-slate-50/50 text-sm text-slate-700 placeholder:text-slate-400 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        />
                        <div v-if="form.errors.name" class="mt-2 text-sm text-red-600">
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700">
                            Address <span class="text-slate-400 font-normal">(optional)</span>
                        </label>
                        <input
                            v-model="form.address"
                            type="text"
                            class="mt-2 block w-full rounded-md border-slate-200 bg-slate-50/50 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        />
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
                            {{ form.processing ? 'Creating...' : 'Create Venue' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>