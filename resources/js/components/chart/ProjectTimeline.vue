<script setup lang="ts">
import type { Measurement, MeasurementValue, Point } from '@/@types/measurement';
import { formatDate } from '@/utils/date';
import { distanceTo } from '@/utils/geo';
import {
    Chart,
    ChartData,
    ChartDataset,
    ChartOptions,
    Legend,
    LinearScale,
    LineElement,
    PointElement,
    TimeScale,
    Tooltip,
} from 'chart.js';
import 'chartjs-adapter-date-fns';
import zoomPlugin from 'chartjs-plugin-zoom';
import { computed, ref } from 'vue';
import { Line } from 'vue-chartjs';

Chart.register(LinearScale, TimeScale, LineElement, PointElement, Tooltip, Legend, zoomPlugin);

const chartRef = ref<{ chart: Chart<'line'> | null } | null>(null);

const props = defineProps<{
    points: Point[];
    pointColors: Record<number, string>;
    measurements: Measurement[];
}>();

type Mode = 'total' | 'horizontal' | 'vertical';
const currentMode = ref<Mode>('total');
const modeLabels: Record<Mode, string> = {
    total: '3D-Gesamt',
    horizontal: '2D-Lage',
    vertical: 'Höhe',
};

const setVisibility = (visible: boolean) => {
    const chart = chartRef.value?.chart;
    if (chart) {
        chart.data.datasets.forEach((_, index) => {
            chart.setDatasetVisibility(index, visible);
        });
        chart.update();
    }
};

const hasData = computed(() => props.measurements.length > 0 && props.points.length > 0);

const timeRange = computed(() => {
    if (props.measurements.length === 0) return { min: undefined, max: undefined };
    const dates = props.measurements.map((m) => new Date(m.datetime).getTime());
    return {
        min: Math.min(...dates),
        max: Math.max(...dates),
    };
});

const chartData = computed<ChartData<'line'>>(() => {
    const datasets: ChartDataset<'line'>[] = props.points.map((point) => {
        const valueMap = new Map(point.measurementValues.map((v) => [v.measurementId, v]));

        let initialValue: MeasurementValue | null = null;
        for (const m of props.measurements) {
            if (valueMap.has(m.id)) {
                initialValue = valueMap.get(m.id)!;
                break;
            }
        }

        const dataPoints = props.measurements
            .map((m) => {
                const val = valueMap.get(m.id);

                if (!val || !initialValue) return null;

                const horizontalDist = distanceTo(initialValue.lat, initialValue.lon, val.lat, val.lon) * 100; // cm
                const verticalDist = (val.height - initialValue.height) * 100; // cm

                let value = 0;
                switch (currentMode.value) {
                    case 'horizontal':
                        value = -horizontalDist;
                        break;
                    case 'vertical':
                        value = verticalDist;
                        break;
                    case 'total':
                    default:
                        value = -Math.hypot(horizontalDist, verticalDist);
                }

                return {
                    x: new Date(m.datetime).getTime(),
                    y: value,
                    measurementName: m.name, // For tooltip
                };
            })
            .filter((p) => p !== null); // Remove nulls to prevent gaps

        const color = props.pointColors[point.id] || '#000000';

        return {
            label: point.name,
            data: dataPoints,
            borderColor: color,
            backgroundColor: color,
            pointRadius: 4,
            pointHoverRadius: 6,
            spanGaps: true,
            clip: false,
        };
    });

    return { datasets };
});

const chartOptions = computed<ChartOptions<'line'>>(() => {
    let yAxisText = '';

    switch (currentMode.value) {
        case 'horizontal':
            yAxisText = 'Horizontale Bewegung [cm]';
            break;
        case 'vertical':
            yAxisText = 'Vertikale Bewegung [cm]';
            break;
        case 'total':
        default:
            yAxisText = 'Gesamte Bewegung [cm]';
            break;
    }

    return {
        responsive: true,
        interaction: {
            mode: 'nearest',
            axis: 'x',
            intersect: false,
        },
        plugins: {
            legend: {
                position: 'bottom',
            },
            /**
             * Claude Sonnet 4.5, 2025-11-25
             * "The tooltip sometimes hides the data point when hovering over it, please fix this behavior."
             */
            tooltip: {
                position: 'nearest',
                caretPadding: 10,
                callbacks: {
                    title: (tooltipItems) => {
                        if (!tooltipItems.length) return '';
                        const item = tooltipItems[0];
                        const raw = item.raw as { x: number; y: number; measurementName: string };
                        return raw.measurementName + ' ' + formatDate(raw.x);
                    },
                    label: (context) => {
                        let label = context.dataset.label + ': ';
                        let y = context.parsed.y;
                        if (y !== null) {
                            label +=
                                y.toLocaleString('de-AT', {
                                    minimumFractionDigits: 1,
                                    maximumFractionDigits: 1,
                                }) + ' cm';
                        }
                        return label;
                    },
                },
            },
            zoom: {
                zoom: {
                    wheel: {
                        enabled: true,
                    },
                    pinch: {
                        enabled: true,
                    },
                    mode: 'y',
                },
                pan: {
                    enabled: true,
                    mode: 'y',
                },
            },
        },
        scales: {
            x: {
                type: 'time',
                time: {
                    unit: 'year',
                    tooltipFormat: 'dd.MM.yyyy',
                },
                title: {
                    display: true,
                    text: 'Datum',
                },
                min: timeRange.value.min,
                max: timeRange.value.max,
            },
            y: {
                title: {
                    display: true,
                    text: yAxisText,
                },
            },
        },
    };
});
</script>

<!--
    Gemini 3 Pro, 2025-11-25
    "Strukturiere das Template etwas sinnvoller und Style es mit Tailwind CSS."
-->
<!--
    Gemini 3 Pro, 2026-02-03
    "Please simplify the Tailwind Styling in ProjectTimeline.vue to reduce it's complexity. Also, make sure that ProjectTimeline.vue scales well and doesn't get too small or bigger than the browser window."
-->
<template>
    <div class="flex h-[85vh] w-[75vw] flex-col rounded bg-white p-4 shadow">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
            <h2 class="text-lg font-bold">Verschiebungsverlauf</h2>

            <div class="flex gap-2 text-sm">
                <button
                    v-for="(label, value) in modeLabels"
                    :key="value"
                    @click="currentMode = value"
                    class="rounded px-3 py-1 transition-colors"
                    :class="currentMode === value ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200'"
                >
                    {{ label }}
                </button>

                <div class="mx-1 w-px bg-gray-300"></div>

                <button @click="setVisibility(true)" class="rounded bg-gray-100 px-3 py-1 hover:bg-gray-200">
                    Alle
                </button>
                <button @click="setVisibility(false)" class="rounded bg-gray-100 px-3 py-1 hover:bg-gray-200">
                    Keine
                </button>
            </div>
        </div>

        <div class="min-h-0 flex-1">
            <div v-if="!hasData" class="flex h-full items-center justify-center text-gray-500">
                Keine Messdaten verfügbar.
            </div>

            <div v-else class="h-full w-full">
                <Line ref="chartRef" :data="chartData" :options="chartOptions" />
            </div>
        </div>
    </div>
</template>
