<script setup lang="ts">
import type { Measurement, MeasurementValue, Point } from '@/@types/measurement';
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
    Title,
    Tooltip,
} from 'chart.js';
import 'chartjs-adapter-date-fns';
import { de } from 'date-fns/locale';
import { computed, ref } from 'vue';
import { Line } from 'vue-chartjs';

Chart.register(LinearScale, PointElement, LineElement, Title, Tooltip, Legend, TimeScale);

const chartRef = ref<any>(null);

const props = defineProps<{
    points: Point[];
    pointColors: Record<number, string>;
    measurements: Measurement[];
}>();

type Mode = 'total' | 'horizontal' | 'vertical';
const currentMode = ref<Mode>('total');
const modeLabels: Record<Mode, string> = {
    total: '3D-Gesamt',
    horizontal: 'Lage',
    vertical: 'Höhe',
};

const setVisibility = (visible: boolean) => {
    const chart = chartRef.value?.chart;
    if (chart) {
        chart.data.datasets.forEach((_: any, index: number) => {
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

                const dx_m = val.x - initialValue.x;
                const dy_m = val.y - initialValue.y;
                const dz_m = val.z - initialValue.z;

                // to cm
                const dx = dx_m * 100;
                const dy = dy_m * 100;
                const dz = dz_m * 100;

                let value = 0;

                // TODO: add explanation comments!
                if (currentMode.value === 'vertical') {
                    value = dz;
                } else if (currentMode.value === 'horizontal') {
                    value = -Math.sqrt(dx * dx + dy * dy);
                } else {
                    value = -Math.sqrt(dx * dx + dy * dy + dz * dz);
                }

                return {
                    x: new Date(m.datetime).getTime(),
                    y: value,
                    measurementName: m.name, // For tooltip
                };
            })
            .filter((p) => p !== null) as any[]; // Remove nulls to prevent gaps

        const color = props.pointColors[point.id] || '#000000';

        return {
            label: point.name,
            data: dataPoints,
            borderColor: color,
            backgroundColor: color,
            pointRadius: 4,
            pointHoverRadius: 6,
            spanGaps: true,
        };
    });

    return { datasets };
});

const chartOptions = computed<ChartOptions<'line'>>(() => {
    let titleText = '';
    let yAxisText = '';

    switch (currentMode.value) {
        case 'horizontal':
            titleText = 'Lageänderungen';
            yAxisText = 'Horizontale Bewegung [cm]';
            break;
        case 'vertical':
            titleText = 'Setzungskurven';
            yAxisText = 'Vertikale Bewegung [cm]';
            break;
        case 'total':
        default:
            titleText = '3D-Veränderungen';
            yAxisText = 'Gesamte Bewegung [cm]';
            break;
    }

    return {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'nearest',
            axis: 'x',
            intersect: false,
        },
        plugins: {
            legend: {
                position: 'bottom',
            },
            title: {
                display: false,
                text: titleText,
            },
            // TODO: add Gemini 3 Pro as the source for the tooltip
            tooltip: {
                position: 'nearest',
                caretPadding: 10,
                callbacks: {
                    title: (tooltipItems) => {
                        if (!tooltipItems.length) return '';
                        const item = tooltipItems[0];
                        const raw = item.raw as { x: number; y: number; measurementName: string };
                        return `${raw.measurementName} (${new Date(raw.x).toLocaleDateString('de-DE')})`;
                    },
                    label: (context) => {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed.y !== null) {
                            label +=
                                context.parsed.y.toLocaleString('de-DE', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2,
                                }) + ' cm';
                        }
                        return label;
                    },
                },
            },
        },
        scales: {
            x: {
                type: 'time',
                time: {
                    unit: 'month',
                    displayFormats: {
                        month: 'MMM yyyy',
                    },
                    tooltipFormat: 'dd.MM.yyyy',
                },
                adapters: {
                    date: {
                        locale: de,
                    },
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

<!-- TODO: Add Gemini 3 Source! -->
<template>
    <div class="rounded-lg bg-white p-4 shadow">
        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-xl font-bold">Verschiebungsverlauf</h2>
            <div class="flex gap-2">
                <button
                    v-for="(label, value) in modeLabels"
                    :key="value"
                    @click="currentMode = value"
                    class="rounded px-3 py-1 text-sm font-medium transition-colors"
                    :class="
                        currentMode === value ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                    "
                >
                    {{ label }}
                </button>
                <div class="mx-1 w-px bg-gray-300"></div>

                <button
                    @click="setVisibility(true)"
                    class="rounded bg-gray-100 px-3 py-1 text-sm font-medium text-gray-600 transition-colors hover:bg-gray-200"
                    title="Alle Punkte einblenden"
                >
                    Alle
                </button>
                <button
                    @click="setVisibility(false)"
                    class="rounded bg-gray-100 px-3 py-1 text-sm font-medium text-gray-600 transition-colors hover:bg-gray-200"
                    title="Alle Punkte ausblenden"
                >
                    Keine
                </button>
            </div>
        </div>

        <div v-if="!hasData" class="flex h-64 items-center justify-center text-gray-500">
            Keine Messdaten verfügbar.
        </div>

        <div v-else class="h-[600px] w-full">
            <Line ref="chartRef" :data="chartData" :options="chartOptions" />
        </div>
    </div>
</template>
