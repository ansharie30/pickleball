<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    tournaments: Array,
});

const showCreateModal = ref(false);

const form = useForm({
    name: '',
    format: 'single_elimination',
    start_date: '',
    end_date: '',
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
    form.post(route('tournaments.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
        },
    });
};

const statusColors = {
    draft: 'bg-slate-100 text-slate-500',
    registration_open: 'bg-blue-100 text-blue-600',
    in_progress: 'bg-amber-100 text-amber-700',
    completed: 'bg-emerald-100 text-emerald-700',
};

const formatLabels = {
    single_elimination: 'Single Elimination',
    double_elimination: 'Double Elimination',
    round_robin: 'Round Robin',
    pool_play: 'Pool Play',
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Tournaments</h1>
                <button
                    @click="openModal"
                    class="inline-flex items-center rounded-md bg-blue-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-400 transition"
                >
                    + New Tournament
                </button>
            </div>
        </template>

        <div class="py-8 sm:py-10">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

                <div
                    v-if="tournaments.length === 0"
                    class="flex flex-col items-center justify-center rounded-xl bg-white py-16 text-center shadow-[0px_14px_34px_0px_rgba(15,23,42,0.06)] ring-1 ring-slate-900/5"
                >
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
                        <svg class="h-6 w-6 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 21h8M12 17v4M7 4h10v4a5 5 0 01-10 0V4zM5 4H3v2a4 4 0 004 4M19 4h2v2a4 4 0 01-4 4" />
                        </svg>
                    </div>
                    <p class="mt-4 text-sm font-medium text-slate-900">No tournaments yet</p>
                    <p class="mt-1 text-sm text-slate-500">Create your first tournament to get started.</p>
                </div>

                <div v-else class="overflow-hidden rounded-xl bg-white shadow-[0px_14px_34px_0px_rgba(15,23,42,0.06)] ring-1 ring-slate-900/5">
                    <Link
                        v-for="(tournament, index) in tournaments"
                        :key="tournament.id"
                        :href="route('tournaments.show', tournament.id)"
                        class="group flex items-center justify-between gap-4 px-6 py-4 transition hover:bg-slate-50"
                        :class="index !== tournaments.length - 1 ? 'border-b border-slate-100' : ''"
                    >
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-900 group-hover:text-blue-600">
                                {{ tournament.name }}
                            </p>
                            <p class="mt-0.5 text-xs text-slate-500">
                                {{ formatLabels[tournament.format] ?? tournament.format }} · {{ tournament.start_date }}
                            </p>
                        </div>

                        <span
                            class="shrink-0 inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                            :class="statusColors[tournament.status]"
                        >
                            {{ tournament.status.replace('_', ' ') }}
                        </span>
                    </Link>
                </div>

            </div>
        </div>

        <Modal :show="showCreateModal" @close="closeModal" max-width="lg">
            <div class="p-6">
                <h2 class="text-base font-semibold text-slate-900 mb-1">Create Tournament</h2>
                <p class="text-sm text-slate-500 mb-5">Set up a new tournament and add divisions once it's created.</p>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Name</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="mt-2 block w-full rounded-md border-slate-200 bg-slate-50/50 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        />
                        <div v-if="form.errors.name" class="mt-2 text-sm text-red-600">
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Format</label>
                        <select
                            v-model="form.format"
                            class="mt-2 block w-full rounded-md border-slate-200 bg-slate-50/50 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="single_elimination">Single Elimination</option>
                            <option value="double_elimination">Double Elimination</option>
                            <option value="round_robin">Round Robin</option>
                            <option value="pool_play">Pool Play</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Start Date</label>
                            <input
                                v-model="form.start_date"
                                type="date"
                                class="mt-2 block w-full rounded-md border-slate-200 bg-slate-50/50 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                            <div v-if="form.errors.start_date" class="mt-2 text-sm text-red-600">
                                {{ form.errors.start_date }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700">End Date <span class="text-slate-400 font-normal">(optional)</span></label>
                            <input
                                v-model="form.end_date"
                                type="date"
                                class="mt-2 block w-full rounded-md border-slate-200 bg-slate-50/50 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
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
                            {{ form.processing ? 'Creating...' : 'Create Tournament' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>