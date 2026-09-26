<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    tournament: Object,
});

const form = useForm({
    name: '',
    skill_level: '',
});

const submit = () => {
    form.post(route('divisions.store', props.tournament.id));
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">New Division</h1>
        </template>

        <div class="py-8 sm:py-10">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-xl bg-white shadow-[0px_14px_34px_0px_rgba(15,23,42,0.06)] ring-1 ring-slate-900/5">
                    <div class="border-b border-slate-100 px-6 py-5 sm:px-8">
                        <h2 class="text-base font-semibold text-slate-900">Division details</h2>
                        <p class="mt-1 text-sm text-slate-500">Add a division to {{ tournament.name }} — e.g. by skill level or category.</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6 px-6 py-6 sm:px-8">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Division Name</label>
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="e.g. Men's Doubles"
                                class="mt-2 block w-full rounded-md border-slate-200 bg-slate-50/50 text-sm text-slate-700 placeholder:text-slate-400 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                            <div v-if="form.errors.name" class="mt-2 text-sm text-red-600">
                                {{ form.errors.name }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700">
                                Skill Level <span class="text-slate-400 font-normal">(optional)</span>
                            </label>
                            <input
                                v-model="form.skill_level"
                                type="text"
                                placeholder="e.g. 3.5"
                                class="mt-2 block w-full rounded-md border-slate-200 bg-slate-50/50 text-sm text-slate-700 placeholder:text-slate-400 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>

                        <div class="flex justify-end border-t border-slate-100 pt-5">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center rounded-md bg-blue-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {{ form.processing ? 'Creating...' : 'Create Division' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>