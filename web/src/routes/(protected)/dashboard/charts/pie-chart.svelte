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
			position: 'bottom',
			floating: true,
			offsetY: 40
		},
		chart: {
			type: 'pie'
		}
	});

	onMount(async () => {
		const apexCharts = (await import('apexcharts')).default;
		chart = new apexCharts(document.querySelector('#chart-' + id), chartOptions);
		chart.render();
	});

	$effect(() => {
		const newData = [data];
		if (chart) {
			chart.updateSeries(newData);
		}
	});
</script>

<Card.Root>
	<Card.Header class="flex items-center gap-2 space-y-0 sm:flex-row">
		<div class="grid flex-1 gap-2">
			<Card.Title>{title}</Card.Title>
		</div>
	</Card.Header>
	<Card.Content class="pb-8">
		<div id="chart-{id}"></div>
	</Card.Content>
</Card.Root>
