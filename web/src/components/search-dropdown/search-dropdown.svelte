<script lang="ts">
    import { X } from '@lucide/svelte';
    import FuzzySearch from 'fuzzy-search';
    import type { ListItem } from './type';

    interface Props {
        initialList: Array<ListItem>,
        selectedList?: Array<ListItem>,
        searchPlaceholder?: string,
        searchQuery?: string,
        showDropdown?: boolean,
        submitButtonLabel?: string,
        cancelButtonLabel?: string,
        title?: string,
        description?: string,
        emptyListMessage?: string,
        onSelect?: (option: ListItem, selectedList: Array<ListItem>) => void,
        onSearch?: (query: string) => void,
        onBlur?: () => void,
        onFocus?: () => void,
        onSubmit?: (selectedList: Array<ListItem>) => void,
        onCancel?: (selectedList: Array<ListItem>) => void,
        onRemove?: (option: ListItem, selectedList: Array<ListItem>) => void,
    }

    let {
        initialList,
        selectedList = $bindable([]),
        searchPlaceholder = 'Search...',
        searchQuery = $bindable(''),
        showDropdown = $bindable(false),
        emptyListMessage = 'List is empty',
        onSelect,
        onBlur,
        onFocus,
        onRemove,
    }: Props = $props();

    let searchableList : Array<ListItem> = $derived.by(() => {
        const selectedIds = new Set(selectedList.map(row => row.id));
        return initialList.filter(row => !selectedIds.has(row.id));
	});
    let searchedList : Array<ListItem> = $derived.by(() => {
        if (searchQuery === '') {
            return searchableList;
        }
        const searcher = new FuzzySearch(searchableList, ['id', 'searchValue'], {
            sort: true,
        });
        return searcher.search(searchQuery);
    });
    

    let inputRef = $state<HTMLInputElement | null>(null);
    let dropdownRef = $state<HTMLDivElement | null>(null);

    function handleFocus(event: FocusEvent) {
        showDropdown = true;
        onFocus?.();
    }
    function handleBlur(event: FocusEvent) {
        showDropdown = false;
        onBlur?.();
    }

    function handleSelect(row: ListItem) {
        selectedList.push(row);
        onSelect?.(row, selectedList);
    }

    function handleRemove(row: ListItem) {
        selectedList = selectedList.filter(item => item.id !== row.id);
        onRemove?.(row, selectedList);
    }
</script>

<div class="flex flex-col gap-1 space-x-1 space-y-1 rounded-md border min-w-0 shadow-xs">
    {#if selectedList.length > 0}
        <div class="flex flex-wrap w-full gap-1 p-2 pb-1">
            {#each selectedList as item, index}
                <div class="flex items-center gap-2 border rounded-md border-gray-200 p-0.5 px-2">
                    <span class="text-nowrap text-sm">{item.label || item.renderSelected?.(item, index)}</span>
                    <button class="text-gray-600 hover:text-red-600 cursor-pointer" onclick={() => handleRemove(item)}>
                        <X class="w-3 h-3" />
                    </button>
                </div>
            {/each}
        </div>
    {/if}
    <div class="px-3 py-1.5 relative" >
        <input 
            bind:this={inputRef}
            id="search-input"
            aria-haspopup="true"
            class="focus:outline-0 w-full text-gray-600 text-sm"
            type="text" 
            bind:value={searchQuery} 
            placeholder={searchPlaceholder} 
            onfocus={(e) => handleFocus(e)}
            onblur={(e) => handleBlur(e)}
        />
        <div 
            bind:this={dropdownRef}
            class:hidden={!showDropdown}
            onmousedown={(e) => e.preventDefault()}
            role="menu"
            aria-orientation="vertical"
            aria-labelledby="search-input"
            tabindex="-1"
            class="min-w-md max-h-80 overflow-y-scroll overflow-x-hidden border 
            border-gray-200 bg-white absolute left-0 z-10 mt-3 shadow-lg ring-1 
            ring-black/5 focus:outline-hidden flex flex-col"
        >
            {#each searchedList as row, index (row.id)}
                <button 
                    onclick={() => {
                        console.log('searchableList', searchableList);
                        handleSelect(row);
                    }}
                    class="p-3 text-left hover:bg-gray-50 text-xs">
                    {row.label || row.renderOption?.(row, index)}
                </button>
            {:else}
                <div class="p-3 text-left text-xs text-gray-300">
                    {emptyListMessage}
                </div>
            {/each}
        </div>
    </div>
</div>