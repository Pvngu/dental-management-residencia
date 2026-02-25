<template>
    <div class="flex bg-white rounded-lg h-full overflow-hidden inset-ring-2 inset-ring-gray-200/80 py-2">
        <div class="w-0.5 h-9 my-auto bg-primary"></div>
        <div class="p-4 flex-1">
            <template v-if="!loading">
                <div class="text-sm text-gray-500 mb-2 font-bold">{{ title }}</div>
                <div class="text-2xl font-semibold mb-1 text-black capitalize">{{ value }}</div>
                <div class="text-xs text-gray-400" v-if="showTrend">
                    <span class="text-green-500 font-bold bg-green-200/30 px-1">{{ trend }}</span> {{ subtitle }}
                </div>
            </template>

            <template v-else>
                <div class="animate-shimmer space-y-2">
                    <div class="h-4 bg-gray-200 rounded w-3/5"></div>
                    <div class="h-8 bg-gray-200 rounded w-1/2"></div>
                    <div class="h-3 bg-gray-200 rounded w-2/5"></div>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        title: {
            type: String,
            required: true
        },
        value: {
            type: [String, Number],
            required: true
        },
        loading: {
            type: Boolean,
            default: false
        },
        trend: {
            type: String,
            default: "+0"
        },
        subtitle: {
            type: String,
            default: ""
        },
        showTrend: {
            type: Boolean,
            default: false
        }
    },
}
</script>

<style scoped>
.animate-shimmer > div {
    position: relative;
    overflow: hidden;
}
.animate-shimmer > div::after {
    content: '';
    position: absolute;
    top: 0;
    left: -150%;
    height: 100%;
    width: 150%;
    background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.6) 50%, rgba(255,255,255,0) 100%);
    animation: shimmer 1.2s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(0); }
    100% { transform: translateX(100%); }
}
</style>