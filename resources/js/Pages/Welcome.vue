<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

const features = [
    {
        title: 'Live Scoreboard',
        description:
            'Track live matches point-by-point, rally by rally. Score updates sync instantly across every device watching the court.',
        icon: 'M4 6h16M4 12h16M4 18h7',
    },
    {
        title: 'Player Rankings',
        description:
            'DUPR-style skill ratings, win/loss records, and head-to-head history for every player in your club or league.',
        icon: 'M11 3.055A9 9 0 1020.945 13H11V3.055z M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z',
    },
    {
        title: 'Match History',
        description:
            'Every game logged automatically — sets, scores, duration, and MVP moments — searchable across seasons.',
        icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
    },
    {
        title: 'Tournament Brackets',
        description:
            'Generate single or double elimination brackets, round robins, and pool play in seconds, then auto-advance winners.',
        icon: 'M9 3v18M15 3v18M3 9h18M3 15h18',
    },
];
</script>

<template>
    <Head title="Welcome" />
    <div
        class="bg-white text-slate-500"
    >
        <div
            class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden selection:bg-blue-400 selection:text-slate-900"
        >
            <!-- decorative court lines -->
            <div
                class="pointer-events-none absolute inset-0 -z-10 opacity-[0.06] "
                aria-hidden="true"
            >
                <div
                    class="absolute left-1/2 top-0 h-full w-px -translate-x-1/2 bg-current"
                ></div>
                <div
                    class="absolute left-0 top-1/2 h-px w-full -translate-y-1/2 bg-current"
                ></div>
            </div>

            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                <header
                    class="grid grid-cols-2 items-center gap-2 py-10 lg:grid-cols-3"
                >
                    <div
                        class="flex items-center gap-2 lg:col-start-2 lg:justify-center"
                    >
                        <!-- paddle + ball mark -->
                        <svg
                            class="h-9 w-9 text-blue-500"
                            viewBox="0 0 24 24"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <ellipse
                                cx="10.5"
                                cy="9"
                                rx="7"
                                ry="8"
                                transform="rotate(-20 10.5 9)"
                                fill="currentColor"
                                fill-opacity="0.15"
                                stroke="currentColor"
                                stroke-width="1.6"
                            />
                            <path
                                d="M14 15.5 20.5 22"
                                stroke="currentColor"
                                stroke-width="1.6"
                                stroke-linecap="round"
                            />
                            <circle
                                cx="19.5"
                                cy="4.5"
                                r="2.5"
                                fill="currentColor"
                            />
                        </svg>
                        <span
                            class="text-lg font-bold tracking-tight text-slate-900"
                            >PicklePro</span
                        >
                    </div>
                    <nav v-if="canLogin" class="-mx-3 flex flex-1 justify-end">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="rounded-md px-3 py-2 text-slate-900 ring-1 ring-transparent transition hover:text-slate-600 focus:outline-none focus-visible:ring-blue-500"
                        >
                            Dashboard
                        </Link>

                        <!-- <template v-else>
                            <Link
                                :href="route('login')"
                                class="rounded-md px-3 py-2 text-slate-900 ring-1 ring-transparent transition hover:text-slate-600 focus:outline-none focus-visible:ring-blue-500"
                            >
                                Log in
                            </Link>

                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="rounded-md bg-slate-900 px-3 py-2 text-white ring-1 ring-transparent transition hover:bg-slate-700 focus:outline-none focus-visible:ring-blue-500"
                            >
                                Register
                            </Link>
                        </template> -->
                    </nav>
                </header>

                <main class="mt-6">
                    <!-- Hero -->
                    <div class="text-center">
                        <span
                            class="inline-flex items-center rounded-full bg-blue-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-blue-600 ring-1 ring-inset ring-blue-500/20"
                        >
                            Live for the 2026 season
                        </span>
                        <h1
                            class="mt-6 text-4xl font-extrabold tracking-tight text-slate-900 sm:text-5xl lg:text-6xl"
                        >
                            Every rally. Every point.
                            <span class="text-blue-500">Tracked.</span>
                        </h1>
                        <p
                            class="mx-auto mt-5 max-w-2xl text-base leading-relaxed sm:text-lg"
                        >
                            PicklePro is the stats and scoreboard system for
                            pickleball clubs, leagues, and tournaments —
                            live scoring, player rankings, and match history
                            in one place.
                        </p>
                        <div
                            class="mt-8 flex flex-wrap items-center justify-center gap-3"
                        >
                            <Link
                                v-if="$page.props.auth?.user"
                                :href="route('dashboard')"
                                class="rounded-md bg-blue-500 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                            >
                                Go to Dashboard
                            </Link>
                            <template v-else>
                                <Link
                                    v-if="canRegister"
                                    :href="route('register')"
                                    class="rounded-md bg-blue-500 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                                >
                                    Create Free Account
                                </Link>
                                <Link
                                    v-if="canLogin"
                                    :href="route('login')"
                                    class="rounded-md px-5 py-3 text-sm font-semibold text-slate-900 ring-1 ring-slate-300 transition hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                                >
                                    View Live Scores
                                </Link>
                            </template>
                        </div>
                    </div>

                    <!-- Feature grid -->
                    <div
                        class="mt-16 grid gap-6 sm:grid-cols-2 lg:grid-cols-4"
                    >
                        <div
                            v-for="feature in features"
                            :key="feature.title"
                            class="rounded-lg bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.06)] ring-1 ring-slate-900/5 transition duration-300 hover:-translate-y-0.5 hover:shadow-lg"
                        >
                            <div
                                class="flex size-11 shrink-0 items-center justify-center rounded-full bg-blue-500/10"
                            >
                                <svg
                                    class="size-5 text-blue-500"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="1.75"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        :d="feature.icon"
                                    />
                                </svg>
                            </div>
                            <h2
                                class="mt-4 text-lg font-semibold text-slate-900"
                            >
                                {{ feature.title }}
                            </h2>
                            <p class="mt-2 text-sm/relaxed">
                                {{ feature.description }}
                            </p>
                        </div>
                    </div>
                </main>

                <footer
                    class="py-16 text-center text-sm text-slate-500"
                >
                    PicklePro &middot; built on Laravel v{{ laravelVersion }}
                    (PHP v{{ phpVersion }})
                </footer>
            </div>
        </div>
    </div>
</template>