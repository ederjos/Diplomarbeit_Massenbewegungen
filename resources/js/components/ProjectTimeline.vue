<template>
    <div class="p-4 bg-white rounded-lg shadow">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold">Verschiebungsverlauf</h2>
            <div class="flex gap-2">
                <button v-for="mode in modes" :key="mode.value" @click="currentMode = mode.value"
                    class="px-3 py-1 rounded text-sm font-medium transition-colors"
                    :class="currentMode === mode.value ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                    {{ mode.label }}
                </button>
            </div>
        </div>

        <div v-if="loading" class="h-64 flex items-center justify-center text-gray-500">
            Daten werden geladen...
        </div>

        <div v-else-if="!hasData" class="h-64 flex items-center justify-center text-gray-500">
            Keine Messdaten verfügbar.
        </div>

        <div v-else class="h-[600px] w-full">
            <Line :data="chartData" :options="chartOptions" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import {
    Chart as ChartJS,
    LinearScale,
    TimeScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend
} from 'chart.js';
import { Line } from 'vue-chartjs';
import 'chartjs-adapter-date-fns';
import { de } from 'date-fns/locale';

ChartJS.register(
    LinearScale,
    TimeScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend
);

interface MeasurementValue {
    x: number;
    y: number;
    z: number;
    measurement_id: number;
    datetime: string | null;
}

interface Point {
    id: number;
    name: string;
    measurement_values: MeasurementValue[];
}

const props = defineProps<{
    projectId?: number
}>();

const loading = ref(true);
const points = ref<Point[]>([]);
const currentMode = ref<'total' | 'horizontal' | 'vertical'>('total');

const modes = [
    { label: '3D-Gesamt', value: 'total' },
    { label: 'Lage', value: 'horizontal' },
    { label: 'Höhe', value: 'vertical' },
] as const;

const hasData = computed(() => points.value.some(p => p.measurement_values.length > 0));

const fetchPoints = async () => {
    try {
        const id = props.projectId || 1;
        const response = await axios.get(`/api/projects/${id}/points-with-measurements`);
        points.value = response.data;
    } catch (error) {
        console.error('Error fetching points:', error);
    } finally {
        loading.value = false;
    }
};

const getPointColor = (name: string, index: number) => {
    const colors = [
        '#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd',
        '#8c564b', '#e377c2', '#7f7f7f', '#bcbd22', '#17becf'
    ];
    return colors[index % colors.length];
};

const pointStyles = ['rect', 'triangle', 'circle', 'rectRot', 'cross', 'star'];

const chartData = computed(() => {
    // 1. Collect all unique timestamps across all points
    const allTimestamps = new Set<number>();
    points.value.forEach(p => {
        p.measurement_values.forEach(m => {
            if (m.datetime) allTimestamps.add(new Date(m.datetime).getTime());
        });
    });
    const sortedTimestamps = Array.from(allTimestamps).sort((a, b) => a - b);

    // 2. Create datasets
    const datasets = points.value.map((point, index) => {
        // Filter and sort values by date
        const sortedValues = point.measurement_values
            .filter(v => v.datetime)
            .sort((a, b) => {
                const da = new Date(a.datetime!).getTime();
                const db = new Date(b.datetime!).getTime();
                return da - db;
            });

        if (sortedValues.length === 0) return null;

        const initial = sortedValues[0];
        const valueMap = new Map(sortedValues.map(v => [new Date(v.datetime!).getTime(), v]));

        const data = sortedTimestamps.map(timestamp => {
            const val = valueMap.get(timestamp);
            if (!val) return { x: timestamp, y: null };

            const dx = val.x - initial.x;
            const dy = val.y - initial.y;
            const dz = val.z - initial.z;

            // Convert to cm
            const dx_cm = dx * 100;
            const dy_cm = dy * 100;
            const dz_cm = dz * 100;

            let yVal = 0;

            if (currentMode.value === 'horizontal') {
                // Horizontal displacement magnitude, negated to show "shortening" or movement away from reference
                // The legacy charts show negative values for horizontal movement.
                yVal = -Math.sqrt(dx_cm * dx_cm + dy_cm * dy_cm);
            } else if (currentMode.value === 'vertical') {
                // Vertical displacement (Z), usually negative for settlement
                yVal = dz_cm;
            } else {
                // Total 3D displacement, negated as per legacy charts
                yVal = -Math.sqrt(dx_cm * dx_cm + dy_cm * dy_cm + dz_cm * dz_cm);
            }

            return { x: timestamp, y: yVal };
        });

        const color = getPointColor(point.name, index);
        const pointStyle = pointStyles[index % pointStyles.length];

        return {
            label: point.name,
            data: data,
            borderColor: color,
            backgroundColor: color,
            pointStyle: pointStyle,
            pointRadius: 5,
            pointHoverRadius: 7,
            tension: 0.1,
            spanGaps: true
        };
    }).filter(ds => ds !== null);

    return {
        datasets
    };
});

const chartOptions = computed(() => {
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
            mode: 'index' as const,
            intersect: false
        },
        plugins: {
            legend: {
                position: 'bottom' as const,
            },
            tooltip: {
                position: 'nearest' as const,
                caretPadding: 10,
                callbacks: {
                    label: function (context: any) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed.y !== null) {
                            label += context.parsed.y.toLocaleString('de-DE', { minimumFractionDigits: 1, maximumFractionDigits: 2 }) + ' cm';
                        }
                        return label;
                    }
                }
            },
            title: {
                display: true,
                text: titleText
            }
        },
        scales: {
            x: {
                type: 'time' as const,
                time: {
                    unit: 'month' as const,
                    displayFormats: {
                        day: 'dd.MM.yyyy',
                        week: 'dd.MM.yyyy',
                        month: 'MMM yyyy',
                        year: 'yyyy'
                    },
                    tooltipFormat: 'dd.MM.yyyy'
                },
                adapters: {
                    date: {
                        locale: de
                    }
                },
                ticks: {
                    maxTicksLimit: 12,
                    maxRotation: 45,
                    minRotation: 45
                },
                title: {
                    display: true,
                    text: 'Datum'
                }
            },
            y: {
                title: {
                    display: true,
                    text: yAxisText
                },
                ticks: {
                    callback: function (value: string | number) {
                        if (typeof value === 'number') {
                            return value.toLocaleString('de-DE');
                        }
                        return value;
                    }
                }
            }
        }
    };
});

onMounted(() => {
    fetchPoints();
});
</script>
