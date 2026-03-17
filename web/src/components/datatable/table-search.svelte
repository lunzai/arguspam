<script lang="ts" generics="TData, TValue">
    import type { Table as TableType } from "@tanstack/table-core";
    import { Input } from "$ui/input";
    import type { Column } from "@tanstack/table-core";

    let {
        table,
        attribute,
        title,
        placeholder,
    }: {
        table: TableType<TData>;
        attribute: string;
        title: string;
        placeholder?: string;
    } = $props();
    const column = $derived(table.getColumn(attribute) as Column<TData, TValue>);
    let query = $derived(column.getFilterValue() as string);
</script>

<Input
    type="text"
    placeholder={placeholder ?? title}
    bind:value={query}
    onchange={() => column.setFilterValue(query.trim() ? query.trim() : undefined)}
/>