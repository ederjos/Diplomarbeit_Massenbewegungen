<script setup lang="ts">
// Modern way instead of emit and props for v-model
const activeTab = defineModel<'results' | 'basics'>('activeTab');
</script>

<template>
    <div class="relative right-0">
        <ul
            class="relative flex list-none flex-wrap rounded-md bg-slate-100 px-1.5 py-1.5"
            data-tabs="tabs"
            role="list"
        >
            <!-- Moving white background indicator -->
            <span
                class="absolute z-10 h-9 rounded-md bg-white transition-transform duration-300 ease-in-out"
                :style="{
                    width: '50%',
                    transform: activeTab === 'results' ? 'translateX(0)' : 'translateX(100%)',
                }"
            ></span>
            <li class="z-30 flex-auto text-center">
                <a
                    class="relative z-20 mb-0 flex w-full cursor-pointer items-center justify-center rounded-md border-0 px-0 py-2 text-sm"
                    :class="activeTab === 'results' ? 'font-semibold text-slate-700' : 'text-slate-600'"
                    data-tab-target=""
                    role="tab"
                    @click="activeTab = 'results'"
                >
                    Ergebnisse
                </a>
            </li>
            <li class="z-30 flex-auto text-center">
                <a
                    class="relative z-20 mb-0 flex w-full cursor-pointer items-center justify-center rounded-lg border-0 px-0 py-2 text-sm"
                    :class="activeTab === 'basics' ? 'font-semibold text-slate-700' : 'text-slate-600'"
                    data-tab-target=""
                    role="tab"
                    @click="activeTab = 'basics'"
                >
                    Grundlagen
                </a>
            </li>
        </ul>
    </div>
</template>
