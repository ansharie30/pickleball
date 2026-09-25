<script setup>
defineProps({
    // Pixel size of the icon square
    size: {
        type: Number,
        default: 36,
    },
    // Show the "PicklePro" wordmark next to the icon
    showText: {
        type: Boolean,
        default: true,
    },
    // Show the small tagline under the wordmark (only used in the stacked layout)
    showTagline: {
        type: Boolean,
        default: false,
    },
    // 'inline' = icon + text side by side (navbars). 'stacked' = icon above text (splash/auth screens).
    layout: {
        type: String,
        default: 'inline',
    },
});
</script>

<template>
    <div
        :class="layout === 'stacked' ? 'flex flex-col items-center gap-2' : 'flex items-center gap-2'"
    >
        <svg
            :width="size"
            :height="size"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.5"
            class="shrink-0 text-blue-500"
            aria-hidden="true"
        >
            <!-- Paddle face -->
            <ellipse cx="12" cy="8.5" rx="6" ry="7" />
            <!-- Handle -->
            <line x1="12" y1="15.5" x2="12" y2="22" stroke-width="2.5" stroke-linecap="round" />
            <!-- Grip wrap lines -->
            <line x1="10" y1="18" x2="14" y2="18" stroke-width="1" />
            <line x1="10" y1="20" x2="14" y2="20" stroke-width="1" />
            <!-- Paddle face texture holes -->
            <circle cx="9.5" cy="7" r="0.4" fill="currentColor" stroke="none" />
            <circle cx="12" cy="6" r="0.4" fill="currentColor" stroke="none" />
            <circle cx="14.5" cy="7" r="0.4" fill="currentColor" stroke="none" />
            <circle cx="9.5" cy="10" r="0.4" fill="currentColor" stroke="none" />
            <circle cx="12" cy="11" r="0.4" fill="currentColor" stroke="none" />
            <circle cx="14.5" cy="10" r="0.4" fill="currentColor" stroke="none" />
        </svg>

        <div v-if="showText" :class="layout === 'stacked' ? 'text-center' : ''">
            <span class="text-lg font-bold tracking-tight text-slate-900">
                PicklePro
            </span>
            <p
                v-if="showTagline"
                class="mt-0.5 text-xs text-slate-500"
            >
                Pickleball stats &amp; scoreboard
            </p>
        </div>
    </div>
</template>