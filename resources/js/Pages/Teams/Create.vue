<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    division: Object,
    players: Array,
});

const form = useForm({
    team_name: '',
    player_one_id: '',
    player_two_id: '',
});

const submit = () => {
    form.post(route('teams.store', props.division.id));
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Register Team</h1>
        </template>

        <div class="py-8 sm:py-10">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-xl bg-white shadow-[0px_14px_34px_0px_rgba(15,23,42,0.06)] ring-1 ring-slate-900/5">
                    <div class="border-b border-slate-100 px-6 py-5 sm:px-8">
                        <h2 class="text-base font-semibold text-slate-900">Team details</h2>
                        <p class="mt-1 text-sm text-slate-500">Add a team to {{ division.name }}.</p>
                    </div>

                    <div v-if="players.length === 0" class="mx-6 mt-6 sm:mx-8 rounded-md bg-amber-50 px-4 py-3 text-sm text-amber-700 ring-1 ring-inset ring-amber-200">
                        No registered players yet. Players need to create an account before they can be added to a team.
                    </div>

                    <form @submit.prevent="submit" class="space-y-6 px-6 py-6 sm:px-8">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Player 1</label>
                            <select
                                v-model="form.player_one_id"
                                class="mt-2 block w-full rounded-md border-slate-200 bg-slate-50/50 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="" disabled>Select a player</option>
                                <option v-for="player in players" :key="player.id" :value="player.id">
                                    {{ player.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.player_one_id" class="mt-2 text-sm text-red-600">
                                {{ form.errors.player_one_id }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700">
                                Player 2 <span class="text-slate-400 font-normal">(leave blank for singles)</span>
                            </label>
                            <select
                                v-model="form.player_two_id"
                                class="mt-2 block w-full rounded-md border-slate-200 bg-slate-50/50 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">— None (Singles) —</option>
                                <option v-for="player in players" :key="player.id" :value="player.id">
                                    {{ player.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.player_two_id" class="mt-2 text-sm text-red-600">
                                {{ form.errors.player_two_id }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700">
                                Team Name <span class="text-slate-400 font-normal">(optional — auto-generated if blank)</span>
                            </label>
                            <input
                                v-model="form.team_name"
                                type="text"
                                placeholder="e.g. The Dinkers"
                                class="mt-2 block w-full rounded-md border-slate-200 bg-slate-50/50 text-sm text-slate-700 placeholder:text-slate-400 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>

                        <div class="flex justify-end border-t border-slate-100 pt-5">
                            <button
                                type="submit"
                                :disabled="form.processing || players.length === 0"
                                class="inline-flex items-center rounded-md bg-blue-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {{ form.processing ? 'Registering...' : 'Register Team' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>