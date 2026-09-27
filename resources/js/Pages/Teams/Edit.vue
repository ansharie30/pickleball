<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    team: Object,
    players: Array,
    positions: Array,
});

const configuredSize = Number(props.team.division?.tournament?.team_size) || 2;
const fixedRoster = configuredSize > 2;
const hasPositions = props.positions?.length > 0;
const slotCount = fixedRoster ? configuredSize : 2;
const currentPlayers = props.team.players ?? [];

const form = useForm({
    team_name: props.team.name ?? '',
    players: Array.from({ length: slotCount }, (_, index) => ({
        player_profile_id: currentPlayers[index]?.id ?? '',
        jersey_number: currentPlayers[index]?.pivot?.jersey_number ?? '',
        position: currentPlayers[index]?.pivot?.position ?? '',
    })),
});

const limitTwoDigits = (event, player) => {
    const digitsOnly = event.target.value.replace(/\D/g, '').slice(0, 2);
    event.target.value = digitsOnly;
    player.jersey_number = digitsOnly;
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        players: data.players.filter((player) => player.player_profile_id !== ''),
    })).patch(route('teams.update', props.team.id));
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Edit Team</h1>
        </template>

        <div class="py-8 sm:py-10">
            <div class="mx-auto w-full px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-xl bg-white shadow-[0px_14px_34px_0px_rgba(15,23,42,0.06)] ring-1 ring-slate-900/5">
                    <div class="border-b border-slate-100 px-6 py-5 sm:px-8">
                        <h2 class="text-base font-semibold text-slate-900">{{ team.name }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ team.division?.name }} · {{ team.division?.tournament?.name }}</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6 px-6 py-6 sm:px-8">
                        <div>
                            <label for="team-name" class="block text-sm font-semibold text-slate-700">Team Name</label>
                            <input
                                id="team-name"
                                v-model="form.team_name"
                                type="text"
                                maxlength="255"
                                class="mt-2 block w-full rounded-md border-slate-200 bg-slate-50/50 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                            <p class="mt-1 text-xs text-slate-400">Leave blank to generate a name from the players.</p>
                            <p v-if="form.errors.team_name" class="mt-1 text-sm text-red-600">{{ form.errors.team_name }}</p>
                        </div>

                        <div class="space-y-4">
                            <div
                                v-for="(player, index) in form.players"
                                :key="index"
                                class="grid gap-3"
                                :class="hasPositions ? 'sm:grid-cols-[minmax(0,1fr)_18rem_5rem]' : 'sm:grid-cols-[minmax(0,1fr)_5rem]'"
                            >
                                <div>
                                    <label :for="`team-player-${index}`" class="block text-sm font-semibold text-slate-700">Player {{ index + 1 }}</label>
                                    <select
                                        :id="`team-player-${index}`"
                                        v-model.number="player.player_profile_id"
                                        :required="fixedRoster || index === 0"
                                        class="mt-2 block w-full rounded-md border-slate-200 bg-slate-50/50 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                        <option v-if="!fixedRoster && index === 1" value="">— None (Singles) —</option>
                                        <option v-else value="" disabled>Select a player</option>
                                        <option
                                            v-for="availablePlayer in players"
                                            :key="availablePlayer.id"
                                            :value="availablePlayer.id"
                                            :disabled="form.players.some((selected, selectedIndex) => selectedIndex !== index && String(selected.player_profile_id) === String(availablePlayer.id))"
                                        >
                                            {{ availablePlayer.name }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors[`players.${index}.player_profile_id`]" class="mt-1 text-sm text-red-600">
                                        {{ form.errors[`players.${index}.player_profile_id`] }}
                                    </p>
                                </div>
                                <div v-if="hasPositions">
                                    <label :for="`player-position-${index}`" class="block text-sm font-semibold text-slate-700">Position</label>
                                    <select
                                        :id="`player-position-${index}`"
                                        v-model="player.position"
                                        :required="fixedRoster || Boolean(player.player_profile_id)"
                                        :disabled="!fixedRoster && index === 1 && !player.player_profile_id"
                                        class="mt-2 block w-full rounded-md border-slate-200 bg-slate-50/50 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500 disabled:cursor-not-allowed disabled:bg-slate-100"
                                    >
                                        <option value="" disabled>Select position</option>
                                        <option v-for="position in positions" :key="position" :value="position">{{ position }}</option>
                                    </select>
                                    <p v-if="form.errors[`players.${index}.position`]" class="mt-1 text-sm text-red-600">
                                        {{ form.errors[`players.${index}.position`] }}
                                    </p>
                                </div>
                                <div>
                                    <label :for="`jersey-number-${index}`" class="block text-sm font-semibold text-slate-700">Number</label>
                                    <input
                                        :id="`jersey-number-${index}`"
                                        :value="player.jersey_number"
                                        type="text"
                                        inputmode="numeric"
                                        pattern="[0-9]*"
                                        maxlength="2"
                                        :required="fixedRoster || Boolean(player.player_profile_id)"
                                        :disabled="!fixedRoster && index === 1 && !player.player_profile_id"
                                        @input="(e) => limitTwoDigits(e, player)"
                                        class="mt-2 block w-full rounded-md border-slate-200 bg-slate-50/50 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500 disabled:cursor-not-allowed disabled:bg-slate-100"
                                    />
                                    <p v-if="form.errors[`players.${index}.jersey_number`]" class="mt-1 text-sm text-red-600">
                                        {{ form.errors[`players.${index}.jersey_number`] }}
                                    </p>
                                </div>
                            </div>
                            <p v-if="form.errors.players" class="text-sm text-red-600">{{ form.errors.players }}</p>
                        </div>

                        <div class="flex items-center justify-between border-t border-slate-100 pt-5">
                            <Link
                                :href="route('tournaments.show', team.tournament_id)"
                                class="text-sm font-medium text-slate-500 hover:text-slate-700"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing || players.length === 0"
                                class="inline-flex items-center rounded-md bg-blue-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-400 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {{ form.processing ? 'Saving...' : 'Save Team' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>