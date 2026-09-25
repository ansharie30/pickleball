<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-slate-50 px-6 py-10 text-slate-500 selection:bg-blue-400 selection:text-slate-900"
    >
        <div class="pointer-events-none absolute inset-0 opacity-[0.06]" aria-hidden="true">
            <div class="absolute left-1/2 top-0 h-full w-px -translate-x-1/2 bg-slate-900"></div>
            <div class="absolute left-0 top-1/2 h-px w-full -translate-y-1/2 bg-slate-900"></div>
            <div class="absolute left-1/2 top-1/2 h-72 w-72 -translate-x-1/2 -translate-y-1/2 rounded-full border border-slate-900"></div>
        </div>

        <div class="relative w-full max-w-md">
            <Link href="/" class="mb-8 flex items-center justify-center gap-2">
                <svg class="h-9 w-9 text-blue-500" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <ellipse cx="10.5" cy="9" rx="7" ry="8" transform="rotate(-20 10.5 9)" fill="currentColor" fill-opacity="0.15" stroke="currentColor" stroke-width="1.6" />
                    <path d="M14 15.5 20.5 22" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                    <circle cx="19.5" cy="4.5" r="2.5" fill="currentColor" />
                </svg>
                <span class="text-lg font-bold tracking-tight text-slate-900">PicklePro</span>
            </Link>

        <div class="overflow-hidden rounded-xl bg-white px-6 py-7 shadow-[0px_18px_45px_0px_rgba(15,23,42,0.08)] ring-1 ring-slate-900/5 sm:px-8">
            <slot />
        </div>

        <p class="relative mt-6 text-center text-xs text-slate-400">
            Pickleball stats, scoring, and tournament play in one place.
        </p>
        </div>
    </div>
</template>
