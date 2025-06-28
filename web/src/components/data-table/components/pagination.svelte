<script lang="ts">
	import { ChevronLeftIcon, ChevronRightIcon } from '@lucide/svelte/icons';
	import type { PaginationConfig } from '../types';
	import * as Pagination from '$ui/pagination';
	import { MediaQuery } from 'svelte/reactivity';

	interface Props {
		pagination: PaginationConfig;
		siblingCount?: number;
		mobileSiblingCount?: number;
		onChange: (pagination: PaginationConfig) => void;
	}

	let { pagination, siblingCount = 3, mobileSiblingCount = 2, onChange }: Props = $props();

	const isDesktop = new MediaQuery('(min-width: 768px)');

	function handlePageChange(page: number) {
		onChange({
			...pagination,
			currentPage: page
		});
	}
</script>

<Pagination.Root
	count={pagination.total}
	perPage={pagination.perPage}
	siblingCount={isDesktop ? siblingCount : mobileSiblingCount}
	page={pagination.currentPage}
	onPageChange={handlePageChange}
	orientation="horizontal"
>
	{#snippet children({ pages, currentPage })}
		<Pagination.Content>
			<Pagination.Item>
				<Pagination.PrevButton>
					<ChevronLeftIcon class="size-4" />
					<span class="hidden sm:block">Previous</span>
				</Pagination.PrevButton>
			</Pagination.Item>
			{#each pages as page (page.key)}
				{#if page.type === 'ellipsis'}
					<Pagination.Item>
						<Pagination.Ellipsis />
					</Pagination.Item>
				{:else}
					<Pagination.Item>
						<Pagination.Link {page} isActive={currentPage === page.value}>
							{page.value}
						</Pagination.Link>
					</Pagination.Item>
				{/if}
			{/each}
			<Pagination.Item>
				<Pagination.NextButton>
					<span class="hidden sm:block">Next</span>
					<ChevronRightIcon class="size-4" />
				</Pagination.NextButton>
			</Pagination.Item>
		</Pagination.Content>
	{/snippet}
</Pagination.Root>
