<script lang="ts">
	import type { LineChartSeries } from '$lib/models/dashboard';
	import * as Card from '$ui/card';
	import { onMount } from 'svelte';
	import * as Select from '$ui/select';

	let {
		id,
		data,
		title,
		description,
		yaxisLabel,
		filename,
		height = 300
	}: {
		id: string;
		data: LineChartSeries;
		title: string;
		description: string;
		yaxisLabel: string;
		filename: string;
		height?: number;
	} = $props();
	let chart: any;
	let timeRange = $state('14d');
	const selectedLabel = $derived.by(() => {
		switch (timeRange) {
			case '90d':
				return '90 days';
			case '30d':
				return '30 days';
			case '14d':
				return '14 days';
			case '7d':
				return '7 days';
			default:
				return '14 days';
		}
	});

	const filteredData = $derived(
		data.map((series) => {
			return {
				...series,
				data: series.data.filter((item) => {
					const referenceDate = new Date();
					let daysToSubtract = 90;
					if (timeRange === '30d') {
						daysToSubtract = 30;
					} else if (timeRange === '14d') {
						daysToSubtract = 14;
					} else if (timeRange === '7d') {
						daysToSubtract = 7;
					}
					referenceDate.setDate(referenceDate.getDate() - daysToSubtract);
					return new Date(item.x) >= referenceDate;
				})
			};
		})
	);

	const filteredDataTotal = $derived(
		filteredData.map((series) => {
			return {
				name: series.name,
				total: series.data.reduce((acc, item) => acc + item.y, 0)
			};
		})
	);

	const chartOptions = $derived({
		series: filteredData,
		chart: {
			type: 'line',
			height: height,
			toolbar: {
				tools: {
					download: true,
					selection: false,
					zoom: false,
					zoomin: false,
					zoomout: false,
					pan: false,
					reset: false
				},
				export: {
					csv: {
						filename
					},
					svg: {
						filename
					},
					png: {
						filename
					}
				}
			},
			zoom: {
				enabled: false
			}
		},
		xaxis: {
			type: 'datetime',
			labels: {
				datetimeFormatter: {
					year: 'yyyy',
					month: "MMM 'yy",
					day: 'dd MMM',
					hour: ''
				}
			}
		},
		yaxis: {
			title: {
				text: yaxisLabel
			}
		}
	});

	onMount(async () => {
		const apexCharts = (await import('apexcharts')).default;
		chart = new apexCharts(document.querySelector('#chart-' + id), chartOptions);
		chart.render();
	});

	$effect(() => {
		const newData = filteredData;
		if (chart) {
			chart.updateSeries(newData);
		}
	});
</script>

<Card.Root>
	<Card.Header class="flex items-center gap-2 space-y-0 sm:flex-row">
		<div class="grid flex-1 gap-2">
			<Card.Title>{title}</Card.Title>
			<Card.Description>{description} {selectedLabel}</Card.Description>
		</div>
		<Select.Root type="single" bind:value={timeRange}>
			<Select.Trigger class="w-[160px] rounded-lg sm:ml-auto" aria-label="Select a value">
				{selectedLabel}
			</Select.Trigger>
			<Select.Content class="rounded-xl">
				<Select.Item value="90d" class="rounded-lg">90 days</Select.Item>
				<Select.Item value="30d" class="rounded-lg">30 days</Select.Item>
				<Select.Item value="14d" class="rounded-lg">14 days</Select.Item>
				<Select.Item value="7d" class="rounded-lg">7 days</Select.Item>
			</Select.Content>
		</Select.Root>
	</Card.Header>
	<Card.Content>
		<div id="chart-{id}"></div>
	</Card.Content>
	{#if filteredDataTotal.length > 0}
		<Card.Footer class="border-t">
			<div class="text-muted-foreground flex w-full items-center justify-center gap-5 text-sm">
				<div class="flex items-center gap-1">
					<span>Total: </span>{filteredDataTotal.reduce((acc, row) => acc + row.total, 0)}
				</div>
				{#each filteredDataTotal as row, index (row.name)}
					<div class="flex items-center gap-1">
						<span>{row.name}: </span>{row.total}
					</div>
				{/each}
			</div>
		</Card.Footer>
	{/if}
</Card.Root>
