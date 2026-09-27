<script setup>
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    match: { type: Object, required: true },
    teamSide: { type: String, required: true },
    limit: { type: Number, required: true },
    canSubstitute: { type: Boolean, default: false },
    dark: { type: Boolean, default: false },
});

const startingIds = ref([]);
const selectedStarterId = ref(null);
const submitting = ref(false);

const roster = computed(() => props.match[`team_${props.teamSide}`]?.players ?? []);
const starters = computed(() => startingIds.value
    .map((id) => roster.value.find((player) => String(player.id) === String(id)))
    .filter(Boolean));
const bench = computed(() => roster.value.filter((player) =>
    !startingIds.value.some((id) => String(id) === String(player.id))
));
const selectedStarter = computed(() => starters.value.find(
    (player) => String(player.id) === String(selectedStarterId.value)
));

const loadStartingIds = () => {
    const saved = props.match.starting_lineups?.[props.teamSide];
    const rosterIds = roster.value.map((player) => player.id);
    const validSaved = Array.isArray(saved)
        ? saved.filter((id, index) => rosterIds.some((rosterId) => String(rosterId) === String(id))
            && saved.findIndex((candidate) => String(candidate) === String(id)) === index)
        : [];
    const nextIds = [...validSaved];

    rosterIds.forEach((id) => {
        if (nextIds.length < props.limit && !nextIds.some((starterId) => String(starterId) === String(id))) {
            nextIds.push(id);
        }
    });

    startingIds.value = nextIds.slice(0, props.limit);
};

watch(
    () => [props.match.starting_lineups, roster.value.map((player) => player.id)],
    loadStartingIds,
    { immediate: true, deep: true }
);

const openSubstitution = (player) => {
    selectedStarterId.value = player.id;
};

const substitute = (benchPlayer) => {
    if (submitting.value || selectedStarterId.value == null) return;

    const previousIds = [...startingIds.value];
    startingIds.value = startingIds.value.map((id) =>
        String(id) === String(selectedStarterId.value) ? benchPlayer.id : id
    );
    submitting.value = true;

    router.patch(route('matches.substitute', props.match.id), {
        team: props.teamSide,
        starter_ids: startingIds.value,
    }, {
        preserveScroll: true,
        onSuccess: () => { selectedStarterId.value = null; },
        onError: () => { startingIds.value = previousIds; },
        onFinish: () => { submitting.value = false; },
    });
};
</script>

<template>
    <div class="mt-4 space-y-4">
        <section>
            <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide" :class="dark ? 'text-gray-400' : 'text-slate-500'">
                Starting {{ limit }}
            </h4>
            <div class="space-y-2">
                <div
                    v-for="(player, index) in starters"
                    :key="player.id"
                    class="rounded-md p-3"
                    :class="dark ? 'bg-white/10' : 'bg-slate-50'"
                >
                    <div class="flex items-start justify-between gap-2">
                        <span class="shrink-0 pt-2 text-xs font-semibold text-slate-400">{{ index + 1 }}</span>
                        <div class="min-w-0 flex-1">
                            <slot name="starter" :player="player" :index="index" />
                            <p v-if="player.pivot?.position" class="mt-1 text-[10px] font-medium" :class="dark ? 'text-gray-400' : 'text-slate-500'">
                                {{ player.pivot.position }}
                            </p>
                        </div>
                        <button
                            v-if="canSubstitute"
                            type="button"
                            :disabled="bench.length === 0 || submitting"
                            class="mt-1 rounded-md border border-slate-300 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40"
                            @click="openSubstitution(player)"
                        >
                            Sub
                        </button>
                    </div>
                </div>
            </div>
            <p v-if="starters.length === 0" class="text-xs" :class="dark ? 'text-gray-400' : 'text-slate-400'">No players registered on this team.</p>
        </section>

        <section v-if="roster.length > starters.length">
            <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide" :class="dark ? 'text-gray-400' : 'text-slate-500'">Bench</h4>
            <ul class="space-y-1.5">
                <li
                    v-for="player in bench"
                    :key="player.id"
                    class="rounded-md px-3 py-2 text-sm"
                    :class="dark ? 'bg-white/10 text-gray-200' : 'bg-slate-50 text-slate-600'"
                >
                    <div class="space-y-1">
                        <slot name="bench" :player="player">
                            <span class="mr-1 text-xs text-slate-400">#{{ player.pivot?.jersey_number ?? '—' }}</span>{{ player.name }}
                        </slot>
                        <p v-if="player.pivot?.position" class="text-[10px] font-medium" :class="dark ? 'text-gray-400' : 'text-slate-500'">
                            {{ player.pivot.position }}
                        </p>
                    </div>
                </li>
            </ul>
        </section>
    </div>

    <div v-if="selectedStarter" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4" @click.self="selectedStarterId = null">
        <section
            role="dialog"
            aria-modal="true"
            aria-labelledby="substitution-title"
            class="w-full max-w-md rounded-lg bg-white p-5 shadow-xl"
        >
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 id="substitution-title" class="text-base font-semibold text-slate-900">Substitute player</h3>
                    <p class="mt-1 text-sm text-slate-500">Choose a bench player to replace {{ selectedStarter.name }}.</p>
                </div>
                <button type="button" class="text-xl leading-none text-slate-400 hover:text-slate-700" aria-label="Close" @click="selectedStarterId = null">&times;</button>
            </div>

            <div v-if="bench.length" class="mt-4 max-h-72 space-y-2 overflow-y-auto">
                <button
                    v-for="player in bench"
                    :key="player.id"
                    type="button"
                    :disabled="submitting"
                    class="flex w-full items-center justify-between rounded-md border border-slate-200 px-3 py-3 text-left text-sm text-slate-700 hover:border-blue-300 hover:bg-blue-50 disabled:opacity-50"
                    @click="substitute(player)"
                >
                    <span class="flex flex-col">
                        <span><span class="mr-1 text-xs text-slate-400">#{{ player.pivot?.jersey_number ?? '—' }}</span>{{ player.name }}</span>
                        <span v-if="player.pivot?.position" class="mt-1 text-xs text-slate-500">{{ player.pivot.position }}</span>
                    </span>
                    <span class="text-xs font-semibold text-blue-600">Select</span>
                </button>
            </div>
            <p v-else class="mt-4 text-sm text-slate-500">There are no bench players available.</p>
        </section>
    </div>
</template>
