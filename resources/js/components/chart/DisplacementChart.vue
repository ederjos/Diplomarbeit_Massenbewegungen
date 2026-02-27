<script setup lang="ts">
import type { LineSeriesOption } from 'echarts/charts';
import { LineChart } from 'echarts/charts';
import type {
    DataZoomComponentOption,
    GridComponentOption,
    LegendComponentOption,
    TooltipComponentOption,
} from 'echarts/components';
import { DataZoomComponent, GridComponent, LegendComponent, TooltipComponent } from 'echarts/components';
import type { ComposeOption, EChartsInitOpts } from 'echarts/core';
import { use } from 'echarts/core';
import { SVGRenderer } from 'echarts/renderers';
import { computed, ref } from 'vue';
import VChart from 'vue-echarts';
import 'echarts/i18n/langDE';
import type { BasePoint, DisplacementsByPointAndMeasurement, Measurement } from '@/@types/measurement';
import { formatDate } from '@/utils/date';

const props = defineProps<{
    points: BasePoint[];
    pointColors: Record<number, string>;
    measurements: Measurement[];
    displacements: DisplacementsByPointAndMeasurement;
}>();

use([LegendComponent, GridComponent, DataZoomComponent, TooltipComponent, LineChart, SVGRenderer]);

// Generated using: https://vue-echarts.dev/#codegen
type EChartsOption = ComposeOption<
    LegendComponentOption | GridComponentOption | DataZoomComponentOption | TooltipComponentOption | LineSeriesOption
>;

const chartRef = ref<InstanceType<typeof VChart> | null>(null);

const initOptions: EChartsInitOpts = {
    renderer: 'svg',
    locale: 'DE',
};

type Mode = 'total' | 'horizontal' | 'vertical';
const currentMode = ref<Mode>('total');
const modeLabels: Record<Mode, { short: string; long: string }> = {
    total: {
        short: '3D-Gesamt',
        long: 'Gesamte Bewegung',
    },
    horizontal: {
        short: '2D-Lage',
        long: 'Horizontale Bewegung',
    },
    vertical: {
        short: 'Höhe',
        long: 'Vertikale Bewegung',
    },
};

const timestampToMeasurementName = new Map(props.measurements.map((m) => [new Date(m.datetime).getTime(), m.name]));

const chartOption = computed<EChartsOption>(() => {
    const series: LineSeriesOption[] = props.points.map((point) => {
        const pointDisplacements = props.displacements[point.id];

        const data: ([number, number] | null)[] = props.measurements.map((measurement) => {
            const displacement = pointDisplacements?.[measurement.id];
            if (!displacement) {
                return null;
            }

            let value = 0;
            switch (currentMode.value) {
                case 'horizontal':
                    // Use projected distance when available
                    value = displacement.projectedDistance ?? displacement.distance2d;
                    break;
                case 'vertical':
                    value = displacement.deltaHeight;
                    break;
                case 'total':
                default:
                    value = displacement.distance3d;
            }

            return [new Date(measurement.datetime).getTime(), value];
        });

        const color = props.pointColors[point.id] || '#000000';

        return {
            type: 'line',
            name: point.name,
            symbol: 'circle',
            symbolSize: 10,
            connectNulls: true,
            itemStyle: {
                color: color,
            },
            lineStyle: {
                color: color,
                width: 4,
            },
            emphasis: {
                focus: 'series',
            },
            data: data,
        };
    });

    return {
        legend: {
            type: 'scroll',
            bottom: 0,
            icon: 'roundRect',
        },
        grid: {
            show: true,
            left: 80,
            top: 20,
            right: 80,
            bottom: 120,
            outerBoundsMode: 'none',
        },
        xAxis: {
            type: 'time',
            name: 'Zeitachse',
            nameLocation: 'middle',
            nameTextStyle: {
                fontSize: 14,
            },
            splitNumber: 15,
            axisLine: {
                show: false,
            },
            axisTick: {
                show: false,
            },
        },
        yAxis: {
            type: 'value',
            name: modeLabels[currentMode.value].long + ' [cm]',
            nameLocation: 'middle',
            nameTextStyle: {
                fontSize: 14,
            },
            axisLabel: {
                formatter: (value) => value.toLocaleString('de-AT', { maximumFractionDigits: 0 }),
            },
        },
        dataZoom: [
            {
                type: 'inside',
                yAxisIndex: 0,
                filterMode: 'none',
            },
            {
                type: 'slider',
                xAxisIndex: 0,
                bottom: 40,
                labelFormatter: (value) => formatDate(value, true, 'de-AT', { year: 'numeric', month: 'short' }),
                filterMode: 'none',
                minValueSpan: 1000 * 3600 * 24 * 30,
                brushSelect: false,
            },
        ],
        tooltip: {
            trigger: 'axis',
            axisPointer: {
                type: 'line',
                label: {
                    formatter: (params) => {
                        const timestamp = params.value as number;
                        return `${timestampToMeasurementName.get(timestamp)} ${formatDate(timestamp)}`;
                    },
                },
            },
            confine: true,
            valueFormatter: (value) => {
                return typeof value === 'number'
                    ? `${value.toLocaleString('de-AT', { minimumFractionDigits: 1, maximumFractionDigits: 1 })} cm`
                    : String(value);
            },
            order: 'valueDesc',
        },
        series: series,
    };
});

function setVisibility(visible: boolean) {
    chartRef.value?.dispatchAction({
        type: 'legendAllSelect',
    });

    if (!visible) {
        chartRef.value?.dispatchAction({
            type: 'legendInverseSelect',
        });
    }
}

function restoreView() {
    chartRef.value?.dispatchAction({
        type: 'restore',
    });
    currentMode.value = 'total';
}
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
    <div class="flex h-[90vh] w-[75vw] flex-col rounded bg-white p-4 shadow">
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
                    {{ label.short }}
                </button>

                <div class="mx-1 w-px bg-gray-300"></div>

                <button @click="setVisibility(true)" class="rounded bg-gray-100 px-3 py-1 hover:bg-gray-200">
                    Alle
                </button>
                <button @click="setVisibility(false)" class="rounded bg-gray-100 px-3 py-1 hover:bg-gray-200">
                    Keine
                </button>

                <div class="mx-1 w-px bg-gray-300"></div>

                <button @click="restoreView()" class="rounded bg-gray-100 px-3 py-1 hover:bg-gray-200">
                    Zurücksetzen
                </button>
            </div>
        </div>

        <div class="h-full min-h-0 w-full flex-1">
            <VChart ref="chartRef" :init-options="initOptions" :option="chartOption" autoresize />
        </div>
    </div>
</template>
