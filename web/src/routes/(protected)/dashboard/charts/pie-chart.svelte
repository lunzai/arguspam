<script lang="ts">
	import type { BarChartSeries } from '$lib/models/dashboard';
	import * as Card from '$ui/card';
	import { onMount } from 'svelte';

	let {
		id,
		data,
		title
	}: {
		id: string;
		data: BarChartSeries;
		title: string;
	} = $props();
	let chart: any;

	const chartOptions = $derived({
		series: [data],
		legend: {
			position: 'bottom' as const,
			floating: true,
			offsetY: 40
		},
		chart: {
			type: 'pie' as const
		}
	});

	onMount(async () => {
		const el = document.getElementById(`chart-${id}`);
		if (!el) return;
		const apexCharts = (await import('apexcharts')).default;
		chart = new apexCharts(el, chartOptions);
		chart.render();
	});

	$effect(() => {
		const newData = [data];
		if (chart) {
			chart.updateSeries(newData);
		}
	});
</script>

<Card.Root class="border-0 shadow-xs rounded-xl">
	<Card.Header class="flex items-center gap-2 space-y-0 sm:flex-row">
		<div class="grid flex-1 gap-2">
			<Card.Title class="text-sm font-medium text-slate-500">{title}</Card.Title>
		</div>
	</Card.Header>
	<Card.Content class="pb-8">
		<div id="chart-{id}"></div>
	</Card.Content>
</Card.Root>
