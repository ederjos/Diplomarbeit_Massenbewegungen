<script setup lang="ts">
defineProps<{
    label: string;
    value?: string | number | null;
}>();
</script>

<template>
    <tr class="border-b border-slate-200 last:border-b-0">
        <td class="w-48 py-3 pr-4 font-semibold text-slate-600">{{ label }}</td>
        <td class="py-3 text-slate-800">
            <!-- Fallback content: If no slot content is given, render the "value" prop
                That way, content can be given either via value or via slot -->
            <slot>{{ value ?? '' }}</slot>
        </td>
    </tr>
</template>
